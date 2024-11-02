<?php

namespace App\Command;

use App\Client\MtgMeleeClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'melee:tournament-winrates')]
class TournamentWinratesCommand extends Command
{
    public function __construct(
        private readonly MtgMeleeClient $client
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('lastRoundId', InputArgument::REQUIRED, 'Last round id of the tournament')
            ->addArgument('limit', InputArgument::OPTIONAL, 'Limit to the n most played decks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lastRoundId = (int) $input->getArgument('lastRoundId');

        $results = $this->client->getTournamentResults($lastRoundId);

        $aggregates = $this->aggregateResultsByDeck($results);

        uasort($aggregates, fn($a, $b) => $b['totalGames'] <=> $a['totalGames']);

        $limit = $input->getArgument('limit');
        if (is_numeric($limit)) {
            $aggregates = array_slice($aggregates, 0, (int) $limit, true);
        }

        foreach ($aggregates as $key => $val) {
            $winrate = $this->calculateWinRate($val['wins'], $val['loses']);

            $output->writeln($key);
            $output->writeln('WinRate: ' . $winrate . ' %');
            $output->writeln('Total Matches: ' . $val['totalGames']);
            $output->writeln('');
        }

        return Command::SUCCESS;
    }

    private function aggregateResultsByDeck(array $results): array
    {
        $aggregates = [];

        foreach ($results as $result) {
            $deckList = $result['deckList'];
            $aggregates[$deckList]['wins'] = ($aggregates[$deckList]['wins'] ?? 0) + $result['wins'];
            $aggregates[$deckList]['loses'] = ($aggregates[$deckList]['loses'] ?? 0) + $result['loses'];
            $aggregates[$deckList]['totalGames'] = ($aggregates[$deckList]['totalGames'] ?? 0) + 1;
        }

        return $aggregates;
    }

    private function calculateWinRate(int $wins, int $loses): float
    {
        $totalGames = $wins + $loses;
        if ($totalGames === 0) {
            return 0;
        }

        $winrate = ($wins / $totalGames) * 100;

        return round($winrate, 2);
    }
}
