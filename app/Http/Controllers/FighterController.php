<?php

namespace App\Http\Controllers;

use App\Models\Battle;
use App\Models\Fighter;
use App\Models\Jutsu;
use App\Models\Tournament;
use App\Models\User;
use App\Services\FighterGenerator;
use App\Services\ImageGeneration\ShinobiImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FighterController extends Controller
{
    public function __construct(
        protected FighterGenerator $generator,
        protected ShinobiImageService $imageService
    ) {}

    /**
     * Display the current user's shinobi profile.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $fighter = $user?->fighter;

        if (! $fighter) {
            return view('fighters.no_fighter');
        }

        $fighter->load([
            'jutsus',
            'xpTransactions' => fn ($q) => $q->latest()->take(10),
        ]);

        $nextTournament = Tournament::whereIn('status', [Tournament::STATUS_REGISTRATION, Tournament::STATUS_RUNNING])
            ->withCount('entries')
            ->orderBy('starts_at', 'asc')
            ->first();

        $recentBattles = Battle::where('fighter_a_id', $fighter->id)
            ->orWhere('fighter_b_id', $fighter->id)
            ->with(['fighterA', 'fighterB', 'winner'])
            ->latest()
            ->take(3)
            ->get();

        $availableJutsus = Jutsu::whereNotIn('id', $fighter->jutsus->pluck('id'))->get();

        return view('fighters.show', [
            'fighter' => $fighter,
            'isOwner' => true,
            'wheelTokens' => $user->wheel_tokens,
            'nextTournament' => $nextTournament,
            'recentBattles' => $recentBattles,
            'availableJutsus' => $availableJutsus,
        ]);
    }

    /**
     * Redirect legacy fighter creation page to the official onboarding wizard.
     */
    public function create(Request $request): RedirectResponse
    {
        return redirect()->route('onboarding.index');
    }

    /**
     * API endpoint to roll/reroll a candidate shinobi.
     */
    public function roll(Request $request): JsonResponse
    {
        $chosenClan = $request->query('clan');
        $chosenElement = $request->query('element');
        $candidate = $this->generator->generateCandidate($chosenClan, $chosenElement);

        return response()->json([
            'success' => true,
            'candidate' => $candidate,
        ]);
    }

    /**
     * Store the chosen shinobi.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->fighter()->exists()) {
            return redirect()->route('fighters.index')
                ->with('error', 'Tu possèdes déjà un shinobi enregistré.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:50'],
            'clan' => ['required', 'string', 'in:'.implode(',', array_keys(FighterGenerator::CLANS))],
            'village' => ['required', 'string', 'in:'.implode(',', FighterGenerator::VILLAGES)],
            'primary_element' => ['required', 'string', 'in:'.implode(',', FighterGenerator::ELEMENTS)],
            'secondary_element' => ['nullable', 'string', 'in:'.implode(',', FighterGenerator::ELEMENTS)],
            'specialization' => ['required', 'string', 'in:'.implode(',', FighterGenerator::SPECIALIZATIONS)],
            'stat_nin' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_tai' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_gen' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_int' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_str' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_spd' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_end' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'stat_sea' => ['required', 'numeric', 'min:1.0', 'max:5.0'],
            'jutsu_ids' => ['nullable', 'array'],
            'jutsu_ids.*' => ['exists:jutsus,id'],
            'portrait' => ['nullable', 'string'],
        ]);

        return DB::transaction(function () use ($user, $validated) {
            $clanData = FighterGenerator::CLANS[$validated['clan']];

            $fighter = Fighter::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'archetype' => $validated['specialization'],
                'clan' => $validated['clan'],
                'village' => $validated['village'],
                'ninja_rank' => 'Genin',
                'primary_element' => $validated['primary_element'],
                'secondary_element' => $validated['secondary_element'] ?? null,
                'specialization' => $validated['specialization'],
                'stat_nin' => $validated['stat_nin'],
                'stat_tai' => $validated['stat_tai'],
                'stat_gen' => $validated['stat_gen'],
                'stat_int' => $validated['stat_int'],
                'stat_str' => $validated['stat_str'],
                'stat_spd' => $validated['stat_spd'],
                'stat_end' => $validated['stat_end'],
                'stat_sea' => $validated['stat_sea'],
                'power' => (int) round($validated['stat_str'] * 4),
                'speed' => (int) round($validated['stat_spd'] * 4),
                'defense' => (int) round($validated['stat_end'] * 4),
                'control' => (int) round($validated['stat_nin'] * 4),
                'special_power' => $validated['primary_element'].' Secret Technique',
                'talent' => $clanData['passive'],
                'portrait_path' => $validated['portrait'] ?? 'ninja_shadow.png',
                'level' => 1,
                'xp' => 0,
            ]);

            // Attach jutsus
            if (! empty($validated['jutsu_ids'])) {
                $fighter->jutsus()->sync($validated['jutsu_ids']);
            } else {
                $defaultJutsus = Jutsu::inRandomOrder()->limit(3)->pluck('id');
                $fighter->jutsus()->sync($defaultJutsus);
            }

            return redirect()->route('fighters.index')
                ->with('success', "Le Shinobi {$fighter->name} du Clan {$fighter->clan} a été officiellement enrôlé dans l'arène !");
        });
    }

    /**
     * Display a specific shinobi profile.
     */
    public function show(Fighter $fighter): View
    {
        $fighter->load([
            'user',
            'jutsus',
            'xpTransactions' => fn ($q) => $q->latest()->take(10),
        ]);
        $isOwner = auth()->check() && auth()->id() === $fighter->user_id;

        $nextTournament = Tournament::whereIn('status', [Tournament::STATUS_REGISTRATION, Tournament::STATUS_RUNNING])
            ->withCount('entries')
            ->orderBy('starts_at', 'asc')
            ->first();

        $recentBattles = Battle::where('fighter_a_id', $fighter->id)
            ->orWhere('fighter_b_id', $fighter->id)
            ->with(['fighterA', 'fighterB', 'winner'])
            ->latest()
            ->take(3)
            ->get();

        return view('fighters.show', [
            'fighter' => $fighter,
            'isOwner' => $isOwner,
            'wheelTokens' => auth()->user()?->wheel_tokens ?? 0,
            'nextTournament' => $nextTournament,
            'recentBattles' => $recentBattles,
        ]);
    }

    /**
     * Token Shop: spend tokens to alter destiny (reroll stat, add jutsu, change clan).
     */
    public function shopSpend(Request $request, Fighter $fighter): RedirectResponse
    {
        $user = $request->user();
        if ($user->id !== $fighter->user_id) {
            abort(403);
        }

        $action = $request->input('action');

        return DB::transaction(function () use ($user, $fighter, $action) {
            // Lock user record for update to prevent concurrent token spend race conditions
            $lockedUser = User::where('id', $user->id)->lockForUpdate()->first();
            if (! $lockedUser) {
                return back()->with('error', 'Utilisateur introuvable.');
            }

            if ($action === 'reroll_element' && $lockedUser->wheel_tokens >= 1) {
                $lockedUser->decrement('wheel_tokens', 1);
                $oldSecondary = $fighter->secondary_element;
                $elements = array_values(array_diff(FighterGenerator::ELEMENTS, [$fighter->primary_element]));
                $newElem = $elements[array_rand($elements)];
                $fighter->update(['secondary_element' => $newElem]);

                // Purger les Jutsus liés à l'ancien élément secondaire qui ne sont plus compatibles
                $replacedDetails = [];
                if ($oldSecondary && $oldSecondary !== $fighter->primary_element) {
                    $orphanJutsus = $fighter->jutsus()->where('element', $oldSecondary)->get();

                    foreach ($orphanJutsus as $orphan) {
                        $fighter->jutsus()->detach($orphan->id);

                        // Tirage de compensation d'un jutsu du nouvel élément secondaire
                        $replacement = Jutsu::where('element', $newElem)
                            ->whereNotIn('id', $fighter->jutsus()->pluck('jutsus.id'))
                            ->where(function ($q) use ($fighter) {
                                $q->whereNull('clan_required')->orWhere('clan_required', $fighter->clan);
                            })
                            ->inRandomOrder()
                            ->first();

                        if ($replacement) {
                            $fighter->jutsus()->attach($replacement->id);
                            $replacedDetails[] = "[{$orphan->name}] converti en [{$replacement->name}]";
                        } else {
                            $replacedDetails[] = "[{$orphan->name}] purgé";
                        }
                    }
                }

                $conversionMsg = ! empty($replacedDetails) ? ' • Réajustement du grimoire : '.implode(', ', $replacedDetails) : '';

                return back()->with('success', "Élément secondaire éveillé : {$newElem} !{$conversionMsg} (1 jeton utilisé)");
            }

            if ($action === 'unlock_rare_jutsu' && $lockedUser->wheel_tokens >= 3) {
                // Éléments compatibles : élément primaire + élément secondaire (si éveillé) + techniques sans affinité
                $allowedElements = array_filter([$fighter->primary_element, $fighter->secondary_element]);

                // Trouver les Jutsus compatibles non encore possédés
                $compatibleJutsus = Jutsu::whereNotIn('id', $fighter->jutsus()->pluck('jutsus.id'))
                    ->where(function ($q) use ($allowedElements) {
                        $q->whereIn('element', $allowedElements)
                            ->orWhereNull('element');
                    })
                    ->where(function ($q) use ($fighter) {
                        // Clan requis nul ou correspondant au clan actuel
                        $q->whereNull('clan_required')
                            ->orWhere('clan_required', $fighter->clan);
                    })
                    ->inRandomOrder()
                    ->get();

                if ($compatibleJutsus->isEmpty()) {
                    return back()->with('info', 'Tu maîtrises déjà tous les Jutsus compatibles avec tes affinités actuelles !');
                }

                $newJutsu = $compatibleJutsus->first();
                $lockedUser->decrement('wheel_tokens', 3);

                // Gestion de la limite des 5 slots
                $currentJutsus = $fighter->jutsus()->get();
                $replacedMessage = '';

                if ($currentJutsus->count() >= 5) {
                    // Écraser un jutsu existant (en épargnant si possible le jutsu spécifique au clan)
                    $replaceable = $currentJutsus->filter(function ($j) use ($fighter) {
                        return $j->clan_required !== $fighter->clan;
                    });
                    $jutsuToReplace = $replaceable->isNotEmpty() ? $replaceable->random() : $currentJutsus->random();
                    $fighter->jutsus()->detach($jutsuToReplace->id);
                    $replacedMessage = " (Emplacement plein 5/5 : [{$jutsuToReplace->name}] a été remplacé)";
                }

                $fighter->jutsus()->attach($newJutsu->id);

                return back()->with('success', "Nouveau Jutsu débloqué : {$newJutsu->name} ({$newJutsu->rank} • {$newJutsu->type}) !{$replacedMessage} (3 jetons utilisés)");
            }

            if ($action === 'change_clan' && $lockedUser->wheel_tokens >= 5) {
                $lockedUser->decrement('wheel_tokens', 5);
                $oldClan = $fighter->clan;
                $clans = array_keys(FighterGenerator::CLANS);
                $otherClans = array_values(array_diff($clans, [$oldClan]));
                $newClan = $otherClans[array_rand($otherClans)];
                $passive = FighterGenerator::CLANS[$newClan]['passive'];
                $newSpec = FighterGenerator::CLANS[$newClan]['favored_spec'] ?? $fighter->specialization;

                // 1. Retirer les techniques secrètes de l'ancien clan
                $oldClanJutsus = Jutsu::where('clan_required', $oldClan)
                    ->orWhere('name', 'like', "%{$oldClan}%")
                    ->pluck('id');
                if ($oldClanJutsus->isNotEmpty()) {
                    $fighter->jutsus()->detach($oldClanJutsus);
                }

                // 2. Transférer le clan et le passif
                $fighter->update([
                    'clan' => $newClan,
                    'talent' => $passive,
                    'specialization' => $newSpec,
                ]);

                // 3. Assigner la technique secrète du nouveau clan (en respectant les 5 slots)
                $newClanJutsu = Jutsu::where('clan_required', $newClan)
                    ->orWhere('name', 'like', "%{$newClan}%")
                    ->first();

                if ($newClanJutsu && ! $fighter->jutsus()->where('jutsus.id', $newClanJutsu->id)->exists()) {
                    if ($fighter->jutsus()->count() >= 5) {
                        $jutsuToDrop = $fighter->jutsus()->first();
                        if ($jutsuToDrop) {
                            $fighter->jutsus()->detach($jutsuToDrop->id);
                        }
                    }
                    $fighter->jutsus()->attach($newClanJutsu->id);
                }

                return back()->with('success', "Lignée transcendée ! Tu appartiens désormais au Clan {$newClan}. Ancien jutsu de clan purgé et don secret [{$newClanJutsu?->name}] éveillé ! (5 jetons)");
            }

            return back()->with('error', 'Nombre de jetons insuffisant pour cette action.');
        });
    }

    /**
     * Allocate stat points gained from leveling up (+0.5 per 500 XP).
     */
    public function allocateStat(Request $request, Fighter $fighter): RedirectResponse
    {
        $user = $request->user();
        if ($user->id !== $fighter->user_id) {
            abort(403);
        }

        $stat = $request->validate([
            'stat' => ['required', 'in:stat_nin,stat_tai,stat_gen,stat_int,stat_str,stat_spd,stat_end,stat_sea'],
        ])['stat'];

        if ($fighter->available_stat_points < 0.5) {
            return back()->with('error', 'Aucun point d\'attribut disponible à attribuer.');
        }

        $currentVal = $fighter->{$stat};
        if ($currentVal >= 5.0) {
            return back()->with('error', 'Cette statistique a déjà atteint le plafond Databook de 5.0.');
        }

        $newVal = min(5.0, round($currentVal + 0.5, 1));
        $fighter->update([
            $stat => $newVal,
            'available_stat_points' => max(0, round($fighter->available_stat_points - 0.5, 1)),
        ]);

        return back()->with('success', "+0.5 point attribué à {$stat} (nouvelle valeur : {$newVal}/5.0) !");
    }

    /**
     * Regenerate portrait using Image-to-Image (Img2Img).
     */
    public function regeneratePortrait(Request $request, Fighter $fighter): RedirectResponse
    {
        $user = $request->user();
        if ($user->id !== $fighter->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'photo' => ['nullable', 'image', 'max:6144'],
            'photo_url' => ['nullable', 'string'],
        ]);

        $sourcePhoto = $fighter->portrait_path;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('source_photos', 'public');
            $sourcePhoto = Storage::url($path);
        } elseif (! empty($validated['photo_url'])) {
            $sourcePhoto = $validated['photo_url'];
        }

        $this->imageService->generateForFighter($fighter, $sourcePhoto);

        return back()->with('success', 'Portrait officiel régénéré par transformation Img2Img avec succès !');
    }

    /**
     * Update fighter tactical battle strategy and jutsu priority.
     */
    public function updateStrategy(Request $request, Fighter $fighter): RedirectResponse
    {
        $user = $request->user();
        if ($user->id !== $fighter->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'strategy_stance' => ['required', 'string', 'in:balanced,offensive,defensive,counter'],
            'jutsu_priority_type' => ['required', 'string', 'in:auto,ninjutsu,taijutsu,genjutsu'],
        ], [
            'strategy_stance.in' => 'Posture tactique invalide sélectionnée.',
            'jutsu_priority_type.in' => 'Priorité de Jutsu invalide sélectionnée.',
        ]);

        $fighter->update($validated);

        return back()->with('success', 'Stratégie de combat et posture tactique mises à jour !');
    }
}
