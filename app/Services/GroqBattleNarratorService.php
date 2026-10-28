<?php

namespace App\Services;

use App\Models\Battle;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqBattleNarratorService
{
    /**
     * Generate or retrieve cached AI play-by-play commentary for a battle.
     */
    public function getOrGenerateCommentary(Battle $battle): string
    {
        // 1. Return cached commentary if already generated
        if (! empty($battle->ai_commentary)) {
            return $battle->ai_commentary;
        }

        // 2. Only generate AI commentary if at least one human player is involved (Player vs Bot or Player vs Player)
        // Bot vs Bot matches do not call the AI API to save resources.
        if (! $battle->involvesHumanPlayer()) {
            return '';
        }

        // 3. Build prompt from battle facts and combat logs
        $commentary = $this->generateWithGroq($battle);

        if (! empty($commentary)) {
            $battle->update(['ai_commentary' => $commentary]);

            return $commentary;
        }

        return '';
    }

    /**
     * Call Groq Cloud API with LLaMA 3.3 70B Versatile.
     */
    public function generateWithGroq(Battle $battle): string
    {
        $apiKey = config('services.groq.api_key');
        $model = config('services.groq.model', 'openai/gpt-oss-120b');

        if (empty($apiKey)) {
            Log::warning('GROQ_API_KEY is missing. Skipping AI battle commentary generation.');

            return '';
        }

        $battle->loadMissing(['fighterA', 'fighterB', 'winner', 'match.tournament', 'logs.attacker']);

        $prompt = $this->buildPrompt($battle);

        try {
            $response = Http::withToken($apiKey)
                ->withoutVerifying()
                ->timeout(35)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => "Tu es le commentateur officiel d'arène survolté d'Otaku Arena (ambiance Shōnen épique, Bruce Buffer x Examen Chūnin de Naruto). Ton rôle est de narrer le duel tour par tour avec passion, intensité, suspense et précision martiale. Rédige en français captivant. Utilise des interjections vivantes, décris les éléments (Katon, Doton, etc.), les tactiques de clan, les esquives, la fatigue et célèbre le dénouement final avec panache. Respecte scrupuleusement les chiffres, les dégâts et le vainqueur indiqués dans les logs.",
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 2500,
                ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                if ($content) {
                    return trim($content);
                }
            }

            Log::error('Groq API Error: '.$response->body());
        } catch (\Throwable $e) {
            Log::error('Groq Battle Narrative Exception: '.$e->getMessage());
        }

        return '';
    }

    /**
     * Build structured combat facts prompt.
     */
    private function buildPrompt(Battle $battle): string
    {
        $fA = $battle->fighterA;
        $fB = $battle->fighterB;
        $winner = $battle->winner;
        $tourn = $battle->match?->tournament?->title ?? "Duel d'Arène";

        $prompt = "Voici les données réelles et vérifiées du combat de l'Examen Chūnin :\n";
        $prompt .= "Contexte : {$tourn}\n";
        $prompt .= "Combattant A : {$fA->name} (Clan: {$fA->clan}, Village: {$fA->village}, Affinité: {$fA->primary_element}, PV départ: {$fA->getMaxHp()})\n";
        $prompt .= "Combattant B : {$fB->name} (Clan: {$fB->clan}, Village: {$fB->village}, Affinité: {$fB->primary_element}, PV départ: {$fB->getMaxHp()})\n";
        $prompt .= "Dénouement Final : Victoire de {$winner?->name} (PV finaux A: {$battle->fighter_a_final_hp}, PV finaux B: {$battle->fighter_b_final_hp})\n\n";

        $prompt .= "Détail des actions tour par tour :\n";
        $grouped = $battle->logs->groupBy('round_number');
        foreach ($grouped as $round => $logs) {
            $prompt .= "\n--- TOUR {$round} ---\n";
            foreach ($logs as $log) {
                $attacker = $log->attacker?->name ?? 'Combattant';
                $jutsu = $log->jutsu_used ?: ($log->action_type === 'dodge' ? 'Esquive' : $log->action_type);
                $dmg = $log->damage_dealt;
                $prompt .= "- {$attacker} | Action: {$jutsu} | Dégâts infligés: {$dmg} PV (PV restants A: {$log->hp_remaining_a}, B: {$log->hp_remaining_b})\n";
            }
        }

        $prompt .= "\nInstructions d'écriture capitales :\n";
        $prompt .= "1. Titre percutant avec le nom des deux shinobis (ex: **⚔️ SHINKI UCHIHA vs BORUTO AKIMICHI – Duel de l'Arène ⚔️**).\n";
        $prompt .= "2. [OUVERTURE DU COMMENTATEUR] présentant les combattants par leurs VRAIS noms et clans ({$fA->name} et {$fB->name}), la foule en délire et l'appel de l'arbitre.\n";
        $prompt .= "3. RÈGLE STRICTE SUR LES NOMS : N'UTILISE JAMAIS les lettres 'A' ou 'B' pour désigner les ninjas ni leurs points de vie. Utilise TOUJOURS leurs vrais prénoms : '{$fA->name}' et '{$fB->name}'.\n";
        $prompt .= "4. Pour chaque Tour, structure le récit comme ceci :\n";
        $prompt .= "   - Titre du tour : ## 🔴 TOUR X — [Technique utilisée ou clash]\n";
        $prompt .= "   - Narration haletante et passionnée des échanges, des jutsus lancés, des esquives et de l'intensité.\n";
        $prompt .= "   - À LA FIN DE CHAQUE TOUR : Inclus obligatoirement la ligne récapitulative exacte : '📊 ÉTAT DES SHINOBIS : {$fA->name} : [X] PV | {$fB->name} : [Y] PV'.\n";
        $prompt .= "5. [DÉNOUEMENT & VERDICT FINAL] célébrant le vainqueur officiel ({$winner?->name}) avec le respect des shinobi et l'ovation du public.\n";
        $prompt .= "N'invente aucun tour supplémentaire. Le duel se termine strictement au Tour {$battle->rounds_count}.";

        return $prompt;
    }
}
