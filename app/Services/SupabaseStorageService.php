<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SupabaseStorageService
{
    protected ?string $url;

    protected ?string $key;

    protected string $bucket;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->bucket = config('services.supabase.bucket', 'shinobi');
    }

    /**
     * Check if Supabase Storage credentials are fully configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->url) && ! empty($this->key);
    }

    /**
     * Upload a file either to Supabase Storage (if configured) or fallback to local disk.
     *
     * @return string The accessible public URL of the uploaded file.
     */
    public function upload(UploadedFile|string $file, string $folder = 'portraits', ?string $customFilename = null): string
    {
        $mimeType = 'image/jpeg';
        $extension = 'jpg';

        if ($file instanceof UploadedFile) {
            $contents = file_get_contents($file->getRealPath());
            $mimeType = $file->getMimeType() ?: 'image/jpeg';
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
        } elseif (is_string($file) && file_exists($file)) {
            $contents = file_get_contents($file);
            $mimeType = mime_content_type($file) ?: 'image/jpeg';
            $extension = pathinfo($file, PATHINFO_EXTENSION) ?: 'jpg';
        } else {
            $contents = (string) $file;
        }

        $filename = $customFilename ?: (Str::random(32).'.'.$extension);
        $path = trim($folder, '/').'/'.ltrim($filename, '/');

        // If Supabase is configured, upload to Supabase Storage
        if ($this->isConfigured()) {
            try {
                $endpoint = rtrim($this->url, '/').'/storage/v1/object/'.$this->bucket.'/'.$path;

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->key,
                    'apikey' => $this->key,
                    'Content-Type' => $mimeType,
                    'x-upsert' => 'true',
                ])->withBody($contents, $mimeType)->post($endpoint);

                if ($response->successful()) {
                    $publicUrl = rtrim($this->url, '/').'/storage/v1/object/public/'.$this->bucket.'/'.$path;
                    Log::info('File successfully uploaded to Supabase Storage:', [
                        'path' => $path,
                        'public_url' => $publicUrl,
                    ]);

                    return $publicUrl;
                }

                Log::warning('Supabase Storage upload failed, falling back to local public disk:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'path' => $path,
                ]);
            } catch (\Throwable $e) {
                Log::error('Exception during Supabase Storage upload, falling back to local disk:', [
                    'message' => $e->getMessage(),
                    'path' => $path,
                ]);
            }
        }

        // Fallback: Local public storage disk
        Storage::disk('public')->put($path, $contents);

        return Storage::url($path);
    }

    /**
     * Delete a file from Supabase or local public storage.
     */
    public function delete(string $urlOrPath): bool
    {
        if (empty($urlOrPath)) {
            return false;
        }

        // If it is a Supabase public URL
        if ($this->isConfigured() && str_contains($urlOrPath, '/storage/v1/object/public/'.$this->bucket.'/')) {
            $parts = explode('/storage/v1/object/public/'.$this->bucket.'/', $urlOrPath);
            $path = end($parts);

            try {
                $endpoint = rtrim($this->url, '/').'/storage/v1/object/'.$this->bucket.'/'.$path;
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$this->key,
                    'apikey' => $this->key,
                ])->delete($endpoint);

                return $response->successful();
            } catch (\Throwable $e) {
                Log::error('Error deleting file from Supabase Storage:', [
                    'message' => $e->getMessage(),
                    'path' => $path,
                ]);

                return false;
            }
        }

        // Local storage cleanup
        $cleanPath = preg_replace('#^/storage/#', '', $urlOrPath);
        if (Storage::disk('public')->exists($cleanPath)) {
            return Storage::disk('public')->delete($cleanPath);
        }

        return false;
    }

    /**
     * Download or stream a stored file.
     */
    public function download(string $urlOrPath, string $downloadFilename): Response
    {
        if (str_starts_with($urlOrPath, 'http://') || str_starts_with($urlOrPath, 'https://')) {
            try {
                $response = Http::timeout(15)->get($urlOrPath);
                if ($response->successful()) {
                    $contentType = $response->header('Content-Type') ?: 'image/jpeg';

                    return response($response->body(), 200, [
                        'Content-Type' => $contentType,
                        'Content-Disposition' => 'attachment; filename="'.$downloadFilename.'"',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Could not stream remote file directly, redirecting instead:', [
                    'url' => $urlOrPath,
                    'error' => $e->getMessage(),
                ]);
            }

            return redirect($urlOrPath);
        }

        // Local public storage download
        $cleanPath = preg_replace('#^/storage/#', '', $urlOrPath);
        if (Storage::disk('public')->exists($cleanPath)) {
            return Storage::disk('public')->download($cleanPath, $downloadFilename);
        }

        if (file_exists(public_path($cleanPath))) {
            return response()->download(public_path($cleanPath), $downloadFilename);
        }

        abort(404, 'Fichier source introuvable sur le serveur.');
    }
}
