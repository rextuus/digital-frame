<?php

namespace App\Controller;

use ApiPlatform\Symfony\Bundle\DependencyInjection\Configuration;
use App\Entity\Image;
use App\Service\FrameConfiguration\Form\ConfigurationData;
use App\Service\FrameConfiguration\Form\ConfigurationType;
use App\Service\FrameConfiguration\Form\ConfigurationUpdateData;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Image\ImageData;
use App\Service\Image\ImageService;
use App\Service\Image\ImageStoreService;
use App\Service\SpotifyAuthenticationService;
use App\Service\Synchronization\GreetingSynchronizationService;
use ColorThief\ColorThief;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    private int $mode = 0;

    private const MODES = [
        1 => 'configuration_image',
        2 => 'configuration_spotify',
        3 => 'configuration_greeting'
    ];

    #[Route('/configuration/change', name: 'app_configuration_change')]
    public function change(
        Request $request,
        FrameConfigurationService $configurationService,
        GreetingSynchronizationService $greetingSynchronizationService
    ): Response {
        $mode = (int)$request->query->get('mode');
        if ($mode) {
            $data = $configurationService->getDefaultUpdateData();
            $data->setMode($mode);
            $configurationService->update($data);
        } else {
            $mode = $configurationService->getMode();
            dump($mode);
        }

        // check if new greetings are available and display instant if so TODO make this switchable
        if ($greetingSynchronizationService->checkForNewGreetings()) {
            $data = $configurationService->getDefaultUpdateData();
            $data->setMode(3);
            $data->setNext(false);
            $configurationService->update($data);
            $mode = 3;
        }


        return new JsonResponse(['mode' => $mode, 'isNext' => $configurationService->isNext()]);
    }

    #[Route('/configuration/next', name: 'app_configuration_next')]
    public function next(Request $request, FrameConfigurationService $configurationService): Response
    {
        $data = $configurationService->getDefaultUpdateData();
        $data->setMode(null);
        $data->setNext(false);
        $configurationService->update($data);
        return new JsonResponse(['next' => false]);
    }

    #[Route('/configuration/landing', name: 'app_configuration_landing')]
    public function view(
        Request $request,
        FrameConfigurationService $configurationService,
        ImageService $imageService,
        ImageStoreService $imageStoreService
    ): Response {
        $configurationData = new ConfigurationData();
        $configurationData->setMode(1);
        $configurationData->setNewTag(null);
        $form = $this->createForm(ConfigurationType::class, $configurationData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $isSpotify = $form->get('spotify')->isClicked();
            $isImage = $form->get('image')->isClicked();
            $isStore = $form->get('store')->isClicked();
            $isGreeting = $form->get('greeting')->isClicked();

            $newMode = $configurationService->getMode();
            $next = $form->get('next')->isClicked();

            if (!$next) {
                if ($isGreeting) {
                    $newMode = 3;
                }
                if ($isSpotify) {
                    $newMode = 2;
                }
                if ($isImage) {
                    $newMode = 1;
                }
                if ($isStore) {
                    $imageStoreService->storeCurrentlyDisplayedImage();
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

        $configurationService->getMode();
        return $this->render('configuration/index.html.twig', [
            'form' => $form->createView(),
            'activeButton' => self::MODES[$configurationService->getMode()]
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
            } catch (\Exception $e) {
                $backgroundColor[3] = $e->getTraceAsString();
            }
        }
        return new JsonResponse($backgroundColor);
    }
}
