<?php

namespace App\Controller;

use App\Service\Artsy\ArtsyService;
use App\Service\Favorite\FavoriteService;
use App\Service\Favorite\LastImageDto;
use App\Service\Favorite\ModeToFavoriteConvertProvider;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\Form\ConfigurationData;
use App\Service\FrameConfiguration\Form\ConfigurationType;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Spotify\SpotifyService;
use App\Service\Synchronization\GreetingSynchronizationService;
use ColorThief\ColorThief;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    public function __construct(
        private readonly SpotifyService $spotifyService,
        private readonly ModeToFavoriteConvertProvider $modeToFavoriteConvertProvider,
        private readonly FrameConfigurationService $configurationService
    ) {
    }

    #[Route('/configuration/change', name: 'app_configuration_change')]
    public function change(
        Request $request,
        GreetingSynchronizationService $greetingSynchronizationService
    ): Response {
        // Todo for what is the query param?
        $mode = DisplayMode::tryFrom((int)$request->query->get('mode'));
        if ($mode) {
            $data = $this->configurationService->getDefaultUpdateData();
            $data->setMode($mode);
            $this->configurationService->update($data);
        } else {
            $mode = $this->configurationService->getMode();
        }

        // check if new greetings are available and display instant if so TODO make this switchable
        if ($greetingSynchronizationService->checkForNewGreetings()) {
            $data = $this->configurationService->getDefaultUpdateData();
            $data->setMode(DisplayMode::GREETING);
            $data->setNext(false);
            $this->configurationService->update($data);
            $mode = DisplayMode::GREETING;
        }

        return new JsonResponse(['mode' => $mode, 'isNext' => $this->configurationService->isNext()]);
    }

    /**
     * @deprecated
     */
    #[Route('/configuration/next', name: 'app_configuration_next')]
    public function next(): Response
    {
        $data = $this->configurationService->getDefaultUpdateData();
        $data->setNext(false);
        $this->configurationService->update($data);
        return new JsonResponse(['next' => false]);
    }

    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_configuration_landing')]
    public function view(Request $request, FavoriteService $favoriteService): Response
    {
        $currentMode = $this->configurationService->getMode();

        $configurationData = new ConfigurationData();
        $configurationData->setMode(1);
        $configurationData->setNewTag(null);
        $form = $this->createForm(ConfigurationType::class, $configurationData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $isSpotify = $form->get('spotify')->isClicked();
            $isImage = $form->get('image')->isClicked();
            $isStore = $form->get('store')->isClicked();
            $isArtsy = $form->get('artsy')->isClicked();
            $isGreeting = $form->get('greeting')->isClicked();

            $newMode = $currentMode;
            $next = $form->get('next')->isClicked();

            if (!$next) {
                if ($isArtsy) {
                    $newMode = DisplayMode::ARTSY;
                }
                elseif ($isGreeting) {
                    $newMode = DisplayMode::GREETING;
                }
                elseif ($isSpotify) {
                    $newMode = DisplayMode::SPOTIFY;
                }
                elseif ($isImage) {
                    $newMode = DisplayMode::UNSPLASH;
                }
            }

            // when new mode is given we mark this in config and can wait until it will be changed from stage
            $waitUntilIsSwitchedViaStageController = $currentMode !== $newMode || $next;
            if ($waitUntilIsSwitchedViaStageController) {
                $this->configurationService->setWaitForModeSwitch(true);
            }

            /** @var ConfigurationData $data */
            $data = $form->getData();

            $updateData = $this->configurationService->getDefaultUpdateData();
            $updateData->setMode($newMode);
            $updateData->setNext($next);
            $this->configurationService->update($updateData);

            // update Tag if new one was added
            $currentTag = $data->getTag();
            if ($data->getNewTag()) {
                $currentTag = $data->getNewTag();
            }
            $this->configurationService->setCurrentTag($currentTag);

            if ($isStore){
                $converter = $this->modeToFavoriteConvertProvider->getFittingConverter();
                $favoriteService->storeFavorite($converter->convertToFavoriteEntity());
            }

            // wait for switch
            if ($waitUntilIsSwitchedViaStageController){
                $counter = 0;
                $waitForSwitch = true;
                while ($waitForSwitch){
                    sleep(1);

                    $waitForSwitch = $this->configurationService->isWaitingForModeSwitch();
                    if (!$waitForSwitch){
                        sleep(2);
                    }

                    $counter++;
                    if ($counter > 30){
                        throw new Exception('Timeout waiting for mode switch');
                    }
                }
            }

            return $this->redirectToRoute('app_configuration_landing');
        }

        $converter = $this->modeToFavoriteConvertProvider->getFittingConverter();

        return $this->render('configuration/index.html.twig', [
            'form' => $form->createView(),
            'activeButton' => $this->configurationService->getMode()->getActiveButton(),
            'lastImageDto' => $converter->getLastImageDto()
        ]);
    }

    private function getLastSpotifyImage(): LastImageDto
    {
        $dto = new LastImageDto();
        $metaData = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong();
        $dto->setUrl($metaData['url']);
        $dto->setArtist($metaData['artist']);
        $dto->setTitle($metaData['name'] . ' (' . $metaData['album'] . ')');

        return $dto;
    }

    #[Route('/configuration/background', name: 'app_configuration_background')]
    public function calculateBackgroundColor(Request $request): Response
    {
        $backgroundColor = [0 => 0, 1 => 0, 2 => 0];

        $imageUrl = $request->query->get('url');
        if ($imageUrl) {
            try {
                $backgroundColor = ColorThief::getColor($imageUrl, 6);
            } catch (Exception $e) {
                $backgroundColor[3] = $e->getTraceAsString();
            }
        }
        return new JsonResponse($backgroundColor);
    }
}
