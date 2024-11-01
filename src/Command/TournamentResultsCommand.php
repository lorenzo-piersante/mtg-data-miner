<?php

namespace App\Command;

use App\Client\MtgMeleeClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:tournament-results')]
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

        $offset = 0;
        $limit = 500;

        $results = $this->client->getTournamentResults($lastRoundId, $offset, $limit);

        $resultsCount = count($results);

        while ($resultsCount === $limit) {
            $offset += $limit;

            $additionalResults = $this->client->getTournamentResults($lastRoundId, $offset, $limit);
            $resultsCount = count($additionalResults);

            $results = array_merge($results, $additionalResults);
        }

        $total = count($results);
        $output->writeln("Total: $total");

        foreach ($results as  $result) {
            $output->writeln($result['player']);
            $output->writeln($result['deckList']);
            $output->writeln($result['matchRecord']);
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
