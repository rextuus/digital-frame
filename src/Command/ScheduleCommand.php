<?php

namespace App\Command;

use App\Service\Scheduling\Scheduler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:schedule',
    description: 'Add a short description for your command',
)]
class ScheduleCommand extends Command
{
    public function __construct(private readonly Scheduler $scheduler)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->scheduler->schedule();

        return Command::SUCCESS;
    }
}
