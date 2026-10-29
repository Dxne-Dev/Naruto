<?php

namespace App\Http\Controllers;

use App\Models\PassRequest;
use App\Models\User;
use App\Services\ImageGeneration\ShinobiImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        protected ShinobiImageService $imageService,
    ) {}

    /**
     * Show the Pass Shinobi Illimité checkout screen (700 FCFA / ~1€).
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->has_paid_microtransaction) {
            return redirect()->route('tournaments.open-list')
                ->with('info', 'Tu disposes déjà du Pass Shinobi Illimité ! Tes inscriptions sont illimitées à vie.');
        }

        $chariowBaseUrl = config('services.chariow.product_url', 'https://chariow.com');
        // Inject user identifier into Chariow checkout URL for tracking
        $separator = str_contains($chariowBaseUrl, '?') ? '&' : '?';
        $chariowRedirectUrl = $chariowBaseUrl.$separator.http_build_query([
            'email' => $user->email,
            'custom_user_id' => $user->id,
            'pseudo' => $user->username ?? $user->name,
        ]);

        return view('payments.checkout', [
            'user' => $user,
            'amountFcfa' => 700,
            'amountEuro' => '1.07',
            'chariowUrl' => $chariowRedirectUrl,
            'fromOnboarding' => (bool) $request->query('from_onboarding', false),
            'latestRequest' => $user->latestPassRequest,
        ]);
    }

    /**
     * Return URL after completing payment on Chariow.
     */
    public function success(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Si le webhook Chariow Pulse a déjà validé le paiement
        if ($user->has_paid_microtransaction) {
            return redirect()->route('fighters.my-character')
                ->with('success', 'Félicitations ! Ton paiement de 700 FCFA a été confirmé. Ton Pass Naruto est actif à vie et les Maîtres Artisans forgent actuellement ton portrait officiel HD !');
        }

        // Sinon, le paiement est en cours de traitement / confirmation par la passerelle
        return redirect()->route('payment.checkout')
            ->with('info', 'Merci pour ton paiement ! La confirmation automatique par Chariow est en cours de synchronisation. Ton pass s\'activera dès la validation du paiement.');
    }

    /**
     * Submit a manual pass verification request to the Game Master team.
     */
    public function process(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->has_paid_microtransaction) {
            return redirect()->route('tournaments.open-list')
                ->with('info', 'Tu possèdes déjà le Pass Shinobi Illimité !');
        }

        // Prevent spamming if a pending request is already waiting
        $existingPending = $user->passRequests()->where('status', 'pending')->first();
        if ($existingPending) {
            return redirect()->route('payment.checkout')
                ->with('info', 'Tu as déjà une demande de vérification en cours d\'examen par l\'administration.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', 'in:wave,orange_money,mtn_money,moov_money,card'],
            'phone_or_card' => ['required', 'string', 'max:50'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'user_note' => ['nullable', 'string', 'max:500'],
            'source_photo_file' => ['nullable', 'image', 'max:6144'],
        ], [
            'payment_method.required' => 'Veuillez sélectionner le mode de paiement utilisé.',
            'phone_or_card.required' => 'Veuillez indiquer le numéro de téléphone ou de carte utilisé.',
        ]);

        PassRequest::create([
            'user_id' => $user->id,
            'payment_method' => $validated['payment_method'],
            'phone_or_card' => $validated['phone_or_card'],
            'transaction_reference' => $validated['transaction_reference'] ?? null,
            'user_note' => $validated['user_note'] ?? null,
            'status' => 'pending',
            'user_notified' => false,
        ]);

        // Si l'utilisateur a attaché sa photo de combattant lors de la demande
        if ($request->hasFile('source_photo_file') && $user->fighter) {
            $path = $request->file('source_photo_file')->store('source_photos', 'public');
            $user->fighter->update([
                'source_photo_path' => Storage::url($path),
            ]);
        }

        return redirect()->route('payment.checkout')
            ->with('success', 'Ta demande de vérification a été transmise aux Grands Maîtres de l\'Arène. Dès confirmation de ton paiement, ton pass sera activé et tu seras immédiatement notifié !');
    }

    /**
     * Upload or update fighter source photo for pass activation and portrait forging.
     */
    public function uploadPhoto(Request $request): RedirectResponse
    {
        $user = $request->user();
        $fighter = $user->fighter;

        if (! $fighter) {
            return back()->with('error', 'Aucun combattant assigné à ton compte.');
        }

        $request->validate([
            'source_photo_file' => ['required', 'image', 'max:6144'],
        ], [
            'source_photo_file.required' => 'Veuillez sélectionner une image.',
            'source_photo_file.image' => 'Le fichier doit être une image valide (JPG, PNG, WebP).',
            'source_photo_file.max' => 'L\'image ne doit pas dépasser 6 Mo.',
        ]);

        $uploadedFile = $request->file('source_photo_file');
        $originalName = $uploadedFile->getClientOriginalName();
        $fileSize = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType();

        $path = $uploadedFile->store('source_photos', 'public');
        $fullDiskPath = storage_path('app/public/'.$path);
        $existsOnDisk = file_exists($fullDiskPath);

        Log::info('PaymentController Photo Upload Debug:', [
            'user_id' => $user->id,
            'fighter_id' => $fighter->id,
            'original_name' => $originalName,
            'file_size_bytes' => $fileSize,
            'mime_type' => $mimeType,
            'stored_path' => $path,
            'full_disk_path' => $fullDiskPath,
            'exists_immediately_on_disk' => $existsOnDisk,
            'storage_url' => Storage::url($path),
        ]);

        $fighter->update([
            'source_photo_path' => Storage::url($path),
            'portrait_status' => $user->has_paid_microtransaction ? 'pending' : $fighter->portrait_status,
        ]);

        return back()->with('success', 'Ta photo a été enregistrée avec succès ! Les Maîtres Artisans l\'utiliseront pour forger ton portrait officiel.');
    }

    /**
     * Mark a pass request notification modal as read/seen by user.
     */
    public function acknowledgeNotification(Request $request, PassRequest $passRequest)
    {
        if ($passRequest->user_id === $request->user()->id) {
            $passRequest->update(['user_notified' => true]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Webhook endpoint for live aggregators / Chariow Pulses.
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Chariow Pulse webhook received:', $payload);

        // Find user by custom_user_id or email
        $userId = $payload['custom_user_id']
            ?? $payload['custom_fields']['custom_user_id']
            ?? $payload['user_id']
            ?? null;

        $email = $payload['customer']['email']
            ?? $payload['email']
            ?? null;

        $user = null;
        if ($userId) {
            $user = User::find($userId);
        }
        if (! $user && $email) {
            $user = User::where('email', $email)->first();
        }

        if ($user) {
            $user->update([
                'has_paid_microtransaction' => true,
            ]);

            if ($user->fighter) {
                $user->fighter->update([
                    'portrait_status' => 'pending',
                    'portrait_ordered_at' => now(),
                ]);
            }

            Log::info("Pass Shinobi activated via Pulse for user #{$user->id} ({$user->username})");
        }

        return response()->json(['status' => 'success', 'message' => 'Pulse processed']);
    }
}
