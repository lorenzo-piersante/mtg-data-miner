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
        $this->addArgument('lastRoundId', InputArgument::REQUIRED, 'Last round id of the tournament:');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lastRoundId = (int) $input->getArgument('lastRoundId');

        $results = $this->client->getTournamentResults($lastRoundId);

        $aggregates = [];
        foreach ($results as  $result) {
            if (!array_key_exists($result['deckList'], $aggregates)) {
                $aggregates[$result['deckList']] = [
                    'wins' => $result['wins'],
                    'loses' => $result['loses'],
                    'totalGames' => '1'
                ];
            } else {
                $aggregates[$result['deckList']]['wins'] += $result['wins'];
                $aggregates[$result['deckList']]['loses'] += $result['loses'];
                $aggregates[$result['deckList']]['totalGames'] += 1;
            }
        }

        // Sort $aggregates by 'totalGames' in descending order
        uasort(
            $aggregates, function ($a, $b) {
                return $b['totalGames'] <=> $a['totalGames'];
            }
        );

        foreach ($aggregates as $key => $val) {
            if ($val['wins'] === 0) {
                $winRate = 0;
            } elseif ($val['loses'] === 0) {
                $winRate = 100;
            } else {
                $winRate = round(($val['wins'] / ($val['wins'] + $val['loses'])) * 100, 2);
            }

            $output->writeln($key);
            $output->writeln('WinRate: ' . $winRate . ' %');
            $output->writeln('Total Matches: ' . $val['totalGames']);
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
