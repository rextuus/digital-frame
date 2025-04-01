<?php

namespace App\Controller;

use App\Service\Favorite\FavoriteService;
use App\Service\Favorite\ModeToFavoriteConvertProvider;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\Form\ConfigurationData;
use App\Service\FrameConfiguration\Form\ConfigurationType;
use App\Service\FrameConfiguration\FrameConfigurationService;
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
        private readonly ModeToFavoriteConvertProvider $modeToFavoriteConvertProvider,
        private readonly FrameConfigurationService $configurationService
    ) {
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
        $backgroundColor = $this->configurationService->getBackgroundColorForCurrentMode();
        if ($backgroundColor !== FrameConfigurationService::COLOR_BLUR) {
            $configurationData->setColor($backgroundColor);
        }
        $form = $this->createForm(ConfigurationType::class, $configurationData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $isSpotify = $form->get('spotify')->isClicked();
            $isImage = $form->get('image')->isClicked();
            $isStore = $form->get('store')->isClicked();
            $isArtsy = $form->get('artsy')->isClicked();
            $isGreeting = $form->get('greeting')->isClicked();
            $isNasa = $form->get('nasa')->isClicked();
            $isGreetingInterruption = $form->get('greetingInterruption')->isClicked();
            $isSpotifyInterruption = $form->get('spotifyInterruption')->isClicked();
            $changeColor = $form->get('changeColor')->isClicked();
            $blur = $form->get('blur')->isClicked();

            $newMode = $currentMode;
            $next = $form->get('next')->isClicked();

            // detect new mode
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
                elseif ($isNasa) {
                    $newMode = DisplayMode::NASA;
                }
            }

            // handle setting toggles
            if ($isGreetingInterruption) {
                $this->configurationService->toggleShouldGreetingInterrupt();
            }

            if ($isSpotifyInterruption) {
                $this->configurationService->toggleShouldSpotifyInterrupt();
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

            // change backgroundColor for current mode
            if ($changeColor){
                $this->configurationService->setBackgroundColorForCurrentMode($data->getColor());
            }
            if ($blur){
                $this->configurationService->setBackgroundColorForCurrentMode(FrameConfigurationService::COLOR_BLUR);
            }

            // wait for switch
            if ($waitUntilIsSwitchedViaStageController){
                $counter = 0;
                $waitForSwitch = true;
                while ($waitForSwitch){
                    sleep(1);

                    $waitForSwitch = $this->configurationService->isWaitingForModeSwitch();
                    if (!$waitForSwitch){
                        sleep(1);
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

        $buttonMap = $this->configurationService->getActiveButtonMap();

        return $this->render('configuration/landing.html.twig', [
            'form' => $form->createView(),
            'buttonMap' => $buttonMap,
            'lastImageDto' => $converter->getLastImageDto(),
            'backgroundColor' => $backgroundColor
        ]);
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
