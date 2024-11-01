<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Client\MtgMeleeClient;
use App\Command\TournamentResultsCommand;
use Dotenv\Dotenv;
use Symfony\Component\Console\Application;

$dotenv= Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = new Application();

$mtgMeleeClient = new MtgMeleeClient();
$app->add(new TournamentResultsCommand($mtgMeleeClient));

$app->run();
