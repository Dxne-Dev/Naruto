<?php

use App\Models\Battle;
use App\Services\BattleNarratorService;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$battle = Battle::with(['fighterA', 'fighterB', 'winner', 'match.tournament', 'logs.attacker'])->find(15);
$narrator = app(BattleNarratorService::class);
$narrativeRounds = $narrator->buildNarrativeRounds($battle->logs, $battle->fighterA, $battle->fighterB);

foreach ($narrativeRounds as $r) {
    echo '--- ROUND '.$r['round'].' ---'.PHP_EOL;
    echo 'Titre: '.($r['title'] ?? '').PHP_EOL;
    echo 'Narration: '.($r['narration'] ?? '').PHP_EOL;
    echo 'Action A: '.json_encode($r['action_a'] ?? []).PHP_EOL;
    echo 'Action B: '.json_encode($r['action_b'] ?? []).PHP_EOL;
}

unlink(__FILE__);
