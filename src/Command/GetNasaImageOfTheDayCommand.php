<?php

namespace App\Command;

use App\Entity\NasaImage;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Nasa\NasaService;
use App\Service\Spotify\SpotifyService;
use App\Service\Unsplash\UnsplashApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:get-nasa-image-of-the-day',
    description: 'Add a short description for your command',
)]
class GetNasaImageOfTheDayCommand extends Command
{
    public function __construct(
        private readonly NasaService $nasaService,
        private readonly EntityManagerInterface $entityManager,
        private readonly UnsplashApiService $unsplashApiService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->unsplashApiService->getImageLinksByTag('bengalo');
        dd();

        $io = new SymfonyStyle($input, $output);

        $imageOfTheDay = $this->nasaService->getImageOfTheDay();

        $nasaImage = new NasaImage();
        $nasaImage->setUrl($imageOfTheDay->getUrl());
        $nasaImage->setExplanation($imageOfTheDay->getExplanation());
        $nasaImage->setTitle($imageOfTheDay->getTitle());
        $nasaImage->setDate($imageOfTheDay->getDate());

        $this->entityManager->persist($nasaImage);
        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
