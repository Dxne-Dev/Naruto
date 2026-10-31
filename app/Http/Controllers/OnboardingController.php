<?php

namespace App\Http\Controllers;

use App\Models\Fighter;
use App\Models\Jutsu;
use App\Services\FighterGenerator;
use App\Services\SupabaseStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(
        protected FighterGenerator $fighterGenerator,
        protected SupabaseStorageService $storageService,
    ) {}

    /**
     * Show interactive step-by-step onboarding wizard.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();

        // L'administrateur n'a pas à passer par l'onboarding joueur
        if ($user && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // If user already has a fighter, go to character view
        if ($user->fighter) {
            return redirect()->route('fighters.my-character');
        }

        $clans = FighterGenerator::CLANS;
        $elements = FighterGenerator::ELEMENTS;
        $villages = FighterGenerator::VILLAGES;

        return view('onboarding.index', [
            'clans' => $clans,
            'elements' => $elements,
            'villages' => $villages,
            'user' => $user,
        ]);
    }

    /**
     * Store and finalize character creation from onboarding.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->fighter) {
            return redirect()->route('fighters.my-character');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'village' => ['required', 'string'],
            'clan' => ['required', 'string'],
            'primary_element' => ['required', 'string'],
            'specialization' => ['nullable', 'string'],
            'stat_nin' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_tai' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_gen' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_int' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_str' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_spd' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_end' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_sea' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'source_photo_url' => ['nullable', 'string'],
            'source_photo_file' => ['nullable', 'image', 'max:6144'],
            'paid_microtransaction' => ['nullable', 'boolean'],
        ]);

        $clanData = FighterGenerator::CLANS[$validated['clan']] ?? null;
        $passiveText = $clanData['passive'] ?? 'Volonté du Feu';
        $specialization = $clanData['favored_spec'] ?? ($validated['specialization'] ?? 'Ninjutsu');

        // Handle uploaded/selected source photo
        $sourcePhotoPath = null;
        if ($request->hasFile('source_photo_file')) {
            $uploadedFile = $request->file('source_photo_file');
            $sourcePhotoPath = $this->storageService->upload($uploadedFile, 'source_photos');

            Log::info('Onboarding Photo Upload:', [
                'user_id' => $user->id,
                'source_photo_path' => $sourcePhotoPath,
            ]);
        } elseif (! empty($validated['source_photo_url'])) {
            $sourcePhotoPath = $validated['source_photo_url'];
        }

        // Si l'utilisateur possède déjà le Pass Shinobi (déjà payé au préalable) :
        // Le statut du portrait passe directement à "pending", son paiement reste acquis,
        // et on ne lui redemande jamais de payer !
        $alreadyPaid = (bool) $user->has_paid_microtransaction;
        $wantsToPay = $alreadyPaid || (bool) ($validated['paid_microtransaction'] ?? false);
        $portraitStatus = $wantsToPay ? 'pending' : 'none';

        $fighter = Fighter::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'archetype' => $specialization,
            'clan' => $validated['clan'],
            'village' => $validated['village'],
            'ninja_rank' => 'Genin',
            'primary_element' => $validated['primary_element'],
            'secondary_element' => null,
            'specialization' => $specialization,
            'stat_nin' => (float) $validated['stat_nin'],
            'stat_tai' => (float) $validated['stat_tai'],
            'stat_gen' => (float) $validated['stat_gen'],
            'stat_int' => (float) $validated['stat_int'],
            'stat_str' => (float) $validated['stat_str'],
            'stat_spd' => (float) $validated['stat_spd'],
            'stat_end' => (float) $validated['stat_end'],
            'stat_sea' => (float) $validated['stat_sea'],
            'power' => (int) round($validated['stat_str'] * 4),
            'speed' => (int) round($validated['stat_spd'] * 4),
            'defense' => (int) round($validated['stat_end'] * 4),
            'control' => (int) round($validated['stat_nin'] * 4),
            'special_power' => $validated['primary_element'].' Jutsu',
            'talent' => $passiveText,
            'source_photo_path' => $sourcePhotoPath,
            'portrait_path' => null,
            'portrait_status' => $portraitStatus,
            'portrait_ordered_at' => $wantsToPay ? now() : null,
            'level' => 1,
            'xp' => 0,
            'available_stat_points' => 0,
            'strategy_stance' => 'balanced',
            'jutsu_priority_type' => 'auto',
            'is_bot' => false,
        ]);

        // Attach starter jutsus :
        // 1. Technique élémentaire de l'affinité du Shinobi (ex: Boule de feu Katon, Lame de vent, etc.)
        // 2. Technique d'Académie/Taijutsu de base (Tornade de Konoha)
        // 3. Technique secrète du Clan (Hiden Nara, Akimichi, Yamanaka, Aburame, Senju, Hyuga...) si disponible
        $starterJutsuIds = collect();

        // 1. Elemental Jutsu
        $elementalJutsu = Jutsu::where('element', $fighter->primary_element)
            ->whereIn('rank', ['D', 'C', 'B'])
            ->first();
        if ($elementalJutsu) {
            $starterJutsuIds->push($elementalJutsu->id);
        }

        // 2. Academy / Basic Taijutsu
        $basicTaijutsu = Jutsu::where('name', 'like', '%Tornade de Konoha%')->first();
        if ($basicTaijutsu) {
            $starterJutsuIds->push($basicTaijutsu->id);
        }

        // 3. Clan Hiden / Specific Jutsu
        $clanJutsu = Jutsu::where('name', 'like', "%{$fighter->clan}%")->first();
        if ($clanJutsu) {
            $starterJutsuIds->push($clanJutsu->id);
        }

        // Fallback si moins de 2 jutsus
        if ($starterJutsuIds->count() < 2) {
            $fallbacks = Jutsu::whereIn('rank', ['D', 'C'])->take(2)->pluck('id');
            $starterJutsuIds = $starterJutsuIds->merge($fallbacks);
        }

        $fighter->jutsus()->syncWithoutDetaching($starterJutsuIds->unique());

        // Si l'utilisateur a déjà payé ou ne souhaite pas payer :
        if ($alreadyPaid) {
            return redirect()->route('fighters.my-character')
                ->with('success', 'Ton nouveau Shinobi a été forgé ! Ton Pass Illimité est actif et ton portrait est en cours de création par l\'administration.');
        }

        // Si l'utilisateur a choisi l'option payante et n'avait pas encore payé, on le redirige vers le checkout / Chariow
        if ($wantsToPay) {
            return redirect()->route('payment.checkout', ['from_onboarding' => 1])
                ->with('info', 'Ton Shinobi est forgé ! Finalise maintenant ton Pass Naruto pour débloquer les tournois illimités et ton portrait officiel.');
        }

        return redirect()->route('fighters.my-character')
            ->with('success', 'Ton Shinobi a été éveillé avec succès ! Quota gratuit : 2 participations aux tournois.');
    }
}
