<?php

namespace App\Http\Controllers;

use App\Services\StrategyAdvisorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StrategyController extends Controller
{
    public function __construct(
        protected StrategyAdvisorService $advisorService
    ) {}

    /**
     * Show attack and defense strategy setup.
     */
    public function index(): View|RedirectResponse
    {
        $fighter = auth()->user()->fighter;

        if (! $fighter) {
            return redirect()->route('onboarding.index')
                ->with('info', 'Crée ton shinobi d\'abord pour configurer tes stratégies.');
        }

        $currentStance = $fighter->strategy_stance ?? 'balanced';
        $currentPriority = $fighter->jutsu_priority_type ?? 'highest_power';

        // Precompute full dynamic tactical profile and sensei advice for all 3 stances
        // so Alpine.js or Blade can switch seamlessly in real-time
        $profiles = [
            'aggressive' => $this->advisorService->computeTacticalProfile($fighter, 'aggressive', $currentPriority),
            'balanced' => $this->advisorService->computeTacticalProfile($fighter, 'balanced', $currentPriority),
            'defensive' => $this->advisorService->computeTacticalProfile($fighter, 'defensive', $currentPriority),
        ];

        return view('strategies.index', [
            'fighter' => $fighter,
            'jutsus' => $fighter->jutsus,
            'profiles' => $profiles,
            'currentProfile' => $profiles[$currentStance] ?? $profiles['balanced'],
        ]);
    }

    /**
     * Update strategy parameters.
     */
    public function update(Request $request): RedirectResponse
    {
        $fighter = auth()->user()->fighter;

        if (! $fighter) {
            return redirect()->route('onboarding.index');
        }

        $validated = $request->validate([
            'strategy_stance' => ['required', 'in:balanced,aggressive,defensive,trickster,offensive,counter'],
            'jutsu_priority_type' => ['required', 'in:auto,highest_power,lowest_chakra,element_counter,ninjutsu,taijutsu,genjutsu'],
        ]);

        $fighter->update($validated);

        return back()->with('success', 'Stratégie de combat enregistrée. Elle s\'appliquera automatiquement lors de tes prochains duels.');
    }
}
