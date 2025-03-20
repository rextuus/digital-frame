<?php

namespace App\Controller;

use App\Service\Artsy\ArtsyService;
use App\Service\Favorite\LastImageDto;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\Form\ConfigurationData;
use App\Service\FrameConfiguration\Form\ConfigurationType;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Spotify\SpotifyService;
use App\Service\Synchronization\GreetingSynchronizationService;
use ColorThief\ColorThief;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    public function __construct(
        private readonly SpotifyService $spotifyService,
    ) {
    }

    #[Route('/configuration/change', name: 'app_configuration_change')]
    public function change(
        Request $request,
        FrameConfigurationService $configurationService,
        GreetingSynchronizationService $greetingSynchronizationService
    ): Response {
        $mode = DisplayMode::tryFrom((int)$request->query->get('mode'));
        if ($mode) {
            $data = $configurationService->getDefaultUpdateData();
            $data->setMode($mode);
            $configurationService->update($data);
        } else {
            $mode = $configurationService->getMode();
        }

        // check if new greetings are available and display instant if so TODO make this switchable
        if ($greetingSynchronizationService->checkForNewGreetings()) {
            $data = $configurationService->getDefaultUpdateData();
            $data->setMode(DisplayMode::GREETING);
            $data->setNext(false);
            $configurationService->update($data);
            $mode = DisplayMode::GREETING;
        }


        return new JsonResponse(['mode' => $mode, 'isNext' => $configurationService->isNext()]);
    }

    #[Route('/configuration/next', name: 'app_configuration_next')]
    public function next(Request $request, FrameConfigurationService $configurationService): Response
    {
        $data = $configurationService->getDefaultUpdateData();
        $data->setNext(false);
        $configurationService->update($data);
        return new JsonResponse(['next' => false]);
    }

    #[Route('/', name: 'app_configuration_landing')]
    public function view(
        Request $request,
        FrameConfigurationService $configurationService,
        ArtsyService $artsyService
    ): Response {
        $currentMode = $configurationService->getMode();

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
                if ($isGreeting) {
                    $newMode = DisplayMode::GREETING;
                }
                if ($isSpotify) {
                    $newMode = DisplayMode::SPOTIFY;
                }
                if ($isImage) {
                    $newMode = DisplayMode::UNSPLASH;
                }
                if ($isStore) {
//                    $imageStoreService->storeCurrentlyDisplayedImage();
                }
            }


            /** @var ConfigurationData $data */
            $data = $form->getData();

            $data2 = $configurationService->getDefaultUpdateData();
            $data2->setMode($newMode);
            $data2->setNext($next);
            $configurationService->update($data2);

            $currentTag = $data->getTag();
            if ($data->getNewTag()) {
                $currentTag = $data->getNewTag();
            }

            $configurationService->setCurrentTag($currentTag);

            // ... perform some action, such as saving the task to the database

            return $this->redirectToRoute('app_configuration_landing');
        }

        $lastArtwork = null;
        match ($currentMode){
            DisplayMode::UNSPLASH => $lastArtwork = null,
            DisplayMode::SPOTIFY => $lastArtwork = null,
            DisplayMode::GREETING => $lastArtwork = null,
            DisplayMode::ARTSY => $lastArtwork = null,
        };

        $lastDisplayedArtworkId = $configurationService->getCurrentArtworkId();
        if ($lastDisplayedArtworkId !== null) {
            $lastArtwork = $artsyService->getArtworkById($lastDisplayedArtworkId);
        }

//        $this->getLastSpotifyImage();

        $configurationService->getMode();
        return $this->render('configuration/index.html.twig', [
            'form' => $form->createView(),
            'lastArtwork' => $lastArtwork,
            'activeButton' => $configurationService->getMode()->getActiveButton()
        ]);
    }

    private function getLastSpotifyImage(): LastImageDto
    {
        $dto = new LastImageDto();
        $metaData = $this->spotifyService->getImageUrlOfCurrentlyPlayingSong();
        $dto->setUrl($metaData['url']);
        $dto->setArtist($metaData['artist']);
        $dto->setTitle($metaData['name'].' ('.$metaData['album'].')');

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
            } catch (\Exception $e) {
                $backgroundColor[3] = $e->getTraceAsString();
            }
        }
        return new JsonResponse($backgroundColor);
    }
}
