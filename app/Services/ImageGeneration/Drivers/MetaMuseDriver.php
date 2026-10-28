<?php

namespace App\Services\ImageGeneration\Drivers;

use App\Models\Fighter;
use App\Services\ImageGeneration\Contracts\ShinobiImageGeneratorInterface;
use App\Services\ImageGeneration\ShinobiPromptBuilder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MetaMuseDriver implements ShinobiImageGeneratorInterface
{
    public function __construct(
        protected ShinobiPromptBuilder $promptBuilder,
        protected LocalFallbackDriver $fallbackDriver,
    ) {}

    public function generate(Fighter $fighter, ?string $sourcePhotoUrl = null): string
    {
        $apiUrl = config('services.muse.api_url');
        $apiKey = config('services.muse.api_key');

        // If credentials are not set yet, fallback gracefully to curated high quality shinobi art
        if (! $apiUrl || ! $apiKey) {
            Log::info("Meta Muse API credentials not set. Falling back to local/preset driver for Fighter #{$fighter->id}.");

            return $this->fallbackDriver->generate($fighter, $sourcePhotoUrl);
        }

        // For Img2Img, if no source image is provided, fallback to default thematic art
        $resolvedSource = $this->prepareSourceImage($sourcePhotoUrl, $fighter);
        if (! $resolvedSource) {
            $resolvedSource = $this->fallbackDriver->generate($fighter, null);
        }

        $prompt = $this->promptBuilder->buildPrompt($fighter);
        $negativePrompt = $this->promptBuilder->buildNegativePrompt();

        try {
            // Img2Img payload: supports 'image_url' (Meta Muse format), 'init_image', or 'image'
            $payload = [
                'prompt' => $prompt,
                'negative_prompt' => $negativePrompt,
                'image_url' => $resolvedSource,
                'image' => $resolvedSource,
                'init_image' => $resolvedSource,
                'strength' => 0.68, // Optimal balance: preserves facial structure while converting to anime
                'denoising_strength' => 0.68,
                'guidance_scale' => 8.0,
                'num_inference_steps' => 30,
                'aspect_ratio' => '3:4',
            ];

            $response = Http::withToken($apiKey)
                ->timeout(45)
                ->post($apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                // Supports direct image URL or base64 response from various providers
                $imageUrl = $data['output'][0] ?? ($data['image_url'] ?? ($data['data'][0]['url'] ?? ($data['images'][0]['url'] ?? null)));

                if ($imageUrl) {
                    return $this->downloadAndStore($imageUrl, $fighter);
                }

                $base64 = $data['image_base64'] ?? ($data['images'][0]['b64_json'] ?? null);
                if ($base64) {
                    return $this->storeBase64($base64, $fighter);
                }
            }

            Log::error('Meta Muse API Error: '.$response->body());
        } catch (\Throwable $e) {
            Log::error('Meta Muse exception: '.$e->getMessage());
        }

        return $this->fallbackDriver->generate($fighter, $sourcePhotoUrl);
    }

    /**
     * Prepare source image into a valid URI or base64 string for the API.
     */
    protected function prepareSourceImage(?string $source, Fighter $fighter): ?string
    {
        if (! $source) {
            return null;
        }

        // Already a public URL or data URI
        if (filter_var($source, FILTER_VALIDATE_URL) || str_starts_with($source, 'data:image/')) {
            return $source;
        }

        // If local storage path exists
        if (Storage::disk('public')->exists($source)) {
            $mime = Storage::disk('public')->mimeType($source) ?? 'image/jpeg';
            $content = Storage::disk('public')->get($source);

            return 'data:'.$mime.';base64,'.base64_encode($content);
        }

        return $source;
    }

    /**
     * Download the remote image and save to local storage.
     */
    protected function downloadAndStore(string $remoteUrl, Fighter $fighter): string
    {
        try {
            $contents = file_get_contents($remoteUrl);
            if ($contents) {
                $filename = 'portraits/shinobi_'.$fighter->id.'_'.Str::random(8).'.png';
                Storage::disk('public')->put($filename, $contents);

                return Storage::url($filename);
            }
        } catch (\Throwable $e) {
            Log::warning('Could not download remote portrait: '.$e->getMessage());
        }

        return $remoteUrl;
    }

    /**
     * Store raw base64 image data.
     */
    protected function storeBase64(string $base64, Fighter $fighter): string
    {
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64));
        $filename = 'portraits/shinobi_'.$fighter->id.'_'.Str::random(8).'.png';
        Storage::disk('public')->put($filename, $data);

        return Storage::url($filename);
    }
}
