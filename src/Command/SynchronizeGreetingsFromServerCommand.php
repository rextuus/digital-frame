<?php

namespace App\Command;

use App\Service\Synchronization\GreetingSynchronizationService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:synchronize:greetings-from-server',
    description: 'Synchronizes greetings from and to server',
)]
class SynchronizeGreetingsFromServerCommand extends Command
{
    public function __construct(
        private readonly GreetingSynchronizationService $greetingSynchronizationService
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // get new greetings
        dump($this->greetingSynchronizationService->checkForNewGreetings());
        if ($this->greetingSynchronizationService->checkForNewGreetings()){
            $this->greetingSynchronizationService->synchronizeGreetingsFromServer();
        }

        // send server info about displayed greetings
        // TODO is this necessary for any reason?
        $this->greetingSynchronizationService->synchronizeDisplayedGreetingsToServer();

        return Command::SUCCESS;
    }
}
