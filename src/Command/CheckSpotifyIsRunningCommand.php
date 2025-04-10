<?php

namespace App\Command;

use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Spotify\SpotifyService;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-spotify-is-running',
    description: 'Checks periodically if a track is currently playing on Spotify',
)]
class CheckSpotifyIsRunningCommand extends Command
{
    public function __construct(
        private readonly SpotifyService $spotifyService,
        private readonly FrameConfigurationService $frameConfigurationService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // only check if interruptions are want
        if (!$this->frameConfigurationService->shouldSpotifyInterrupt()) {
            return Command::SUCCESS;
        }

        try {
            $currentTrack = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong();
            if ($currentTrack === []) {
                $io->writeln('No track is currently playing.');

                if ($this->frameConfigurationService->getForcedSpotifyInterruption() !== null) {
                    $this->frameConfigurationService->releaseSpotifyInterruption();
                }
                return Command::SUCCESS;
            }

            $io->writeln('Running');
            if (
                $this->frameConfigurationService->shouldSpotifyInterrupt()
                && $this->frameConfigurationService->getMode() !== DisplayMode::SPOTIFY
            ) {
                $this->frameConfigurationService->forceSpotifyInterruption();
            }

            return Command::SUCCESS;
        } catch (Exception $e) {
            $io->error('An error occurred while checking the currently playing track: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
