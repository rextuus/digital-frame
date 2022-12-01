<?php

namespace App\Controller;

use App\Service\SpotifyAuthenticationService;
use ColorThief\ColorThief;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StageController extends AbstractController
{
    private int $mode = 0;

    private const UNSPLASH_TOKEN = 'IggqUsh5jKqF7WtHOiX64x8BYrLSfC86SyrmySDaWFY';


    #[Route('/stage', name: 'app_stage')]
    public function index(): Response
    {
//        $spotifyAuthenticationService->refreshAccessToken();die();
        return $this->render('stage/index.html.twig', [
            'controller_name' => 'StageController'
        ]);
    }

    #[Route('/stage/spotify', name: 'app_stage_spotify')]
    public function spotify(SpotifyAuthenticationService $spotifyAuthenticationService): Response
    {
        return $this->render('stage/spotify.html.twig', [
            'controller_name' => 'StageController',
            'token' => $spotifyAuthenticationService->getValidAccessToken()
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
