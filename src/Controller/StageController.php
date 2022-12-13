<?php

namespace App\Controller;

use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\SpotifyAuthenticationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StageController extends AbstractController
{
    const MODE_REDIRECTS = [
        1 => 'app_stage_image',
        2 => 'app_stage_spotify',
    ];

    private const UNSPLASH_TOKEN = 'IggqUsh5jKqF7WtHOiX64x8BYrLSfC86SyrmySDaWFY';


    #[Route('/stage', name: 'app_stage')]
    public function index(FrameConfigurationService $configurationService): Response
    {
        $currentMode = $configurationService->getMode();
        return $this->redirectToRoute(self::MODE_REDIRECTS[$currentMode]);
    }

    #[Route('/stage/spotify', name: 'app_stage_spotify')]
    public function spotify(SpotifyAuthenticationService $spotifyAuthenticationService): Response
    {
        return $this->render('stage/spotify.html.twig', [
            'controller_name' => 'StageController',
            'token' => $spotifyAuthenticationService->getValidAccessToken(),
            'marginTop' => '40px',
        ]);
    }

    #[Route('/stage/image', name: 'app_stage_image')]
    public function image(): Response
    {
        return $this->render('stage/image.html.twig', [
            'controller_name' => 'StageController',
            'token' => self::UNSPLASH_TOKEN
        ]);
    }
}
