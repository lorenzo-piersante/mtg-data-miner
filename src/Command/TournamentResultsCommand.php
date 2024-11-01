<?php

namespace App\Command;

use App\Client\MtgMeleeClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'melee:tournament-results')]
class TournamentResultsCommand extends Command
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
        // example: 543546
        $lastRoundId = (int) $input->getArgument('lastRoundId');

        $results = $this->client->getTournamentResults($lastRoundId);

        $total = count($results);
        $output->writeln("Total: $total");
        $output->writeln('');

        foreach ($results as  $result) {
            $output->writeln('Player: ' . $result['player']);
            $output->writeln('Deck: ' . $result['deckList']);
            $output->writeln('Wins: ' . $result['wins']);
            $output->writeln('Loses: ' . $result['loses']);
            $output->writeln('Draws: ' . $result['draws']);
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
