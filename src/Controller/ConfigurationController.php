<?php

namespace App\Controller;

use ApiPlatform\Symfony\Bundle\DependencyInjection\Configuration;
use App\Entity\Image;
use App\Service\FrameConfiguration\Form\ConfigurationData;
use App\Service\FrameConfiguration\Form\ConfigurationType;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Image\ImageData;
use App\Service\Image\ImageService;
use App\Service\Image\ImageStoreService;
use App\Service\SpotifyAuthenticationService;
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
        2 => 'configuration_spotify'
    ];

    #[Route('/configuration/change', name: 'app_configuration_change')]
    public function change(Request $request, FrameConfigurationService $configurationService): Response
    {
        $mode = (int) $request->query->get('mode');
        if ($mode){
            $configurationService->updateConfiguration($mode);
        }else{
            $mode = $configurationService->getMode();
        }
        return new JsonResponse(['mode' => $mode, 'isNext' => $configurationService->isNext()]);
    }

    #[Route('/configuration/next', name: 'app_configuration_next')]
    public function next(Request $request, FrameConfigurationService $configurationService): Response
    {
        $configurationService->updateConfiguration(null, false);
        return new JsonResponse(['next' => false]);
    }

    #[Route('/configuration/landing', name: 'app_configuration_landing')]
    public function view(Request $request, FrameConfigurationService $configurationService, ImageService $imageService, ImageStoreService $imageStoreService): Response
    {
        $configurationData = new ConfigurationData();
        $configurationData->setMode(1);
        $form = $this->createForm(ConfigurationType::class, $configurationData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $isSpotify = $form->get('spotify')->isClicked();
            $isImage = $form->get('image')->isClicked();
            $isStore = $form->get('store')->isClicked();

            $newMode = $configurationService->getMode();
            $next = $form->get('next')->isClicked();

            if (!$next){
                if ($isSpotify){
                    $newMode = 2;
                }
                if ($isImage){
                    $newMode = 1;
                }
                if ($isStore){
                    $imageStoreService->storeCurrentlyDisplayedImage();
                }
            }

            $configurationService->updateConfiguration($newMode, $next);

            $task = $form->getData();


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
        if ($imageUrl){
            try {
                $backgroundColor = ColorThief::getColor($imageUrl, 6);
            } catch (\Exception $e) {
                $backgroundColor[3] = $e->getTraceAsString();

            }
        }
        return new JsonResponse($backgroundColor);
    }
}
