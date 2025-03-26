<?php

namespace App\Command;

use App\Repository\ArtsyImageRepository;
use App\Service\Artsy\ArtsyService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:init-artsy',
    description: 'Add a short description for your command',
)]
class InitArtsyCommand extends Command
{
    public function __construct(
        private readonly ArtsyService $artsyService,
        private readonly ArtsyImageRepository $artsyImageRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->artsyImageRepository->count([]) === 0) {
            $this->artsyService->getArtworks();
            sleep(5);
        }

        $counter = 0;
        while ($counter < 100) {
            $this->artsyService->storeArtworksFromNextPageUrlInDatabase();
            sleep(3);
            $counter++;
        }

        return Command::SUCCESS;
    }
}
