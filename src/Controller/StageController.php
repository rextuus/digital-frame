<?php

namespace App\Controller;

use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Greeting\Form\GreetingData;
use App\Service\Greeting\GreetingService;
use App\Service\SpotifyAuthenticationService;
use App\Service\Synchronization\GreetingSynchronizationService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stage')]
class StageController extends AbstractController
{
    const MODE_REDIRECTS = [
        1 => 'app_stage_image',
        2 => 'app_stage_spotify',
        3 => 'app_stage_greeting',
    ];

    private const UNSPLASH_TOKEN = 'IggqUsh5jKqF7WtHOiX64x8BYrLSfC86SyrmySDaWFY';


    #[Route('/', name: 'app_stage')]
    public function index(
        FrameConfigurationService $configurationService,
        GreetingSynchronizationService $greetingSynchronizationService
    ): Response {
        $currentMode = $configurationService->getMode();
        if ($greetingSynchronizationService->checkForNewGreetings()){
            $data = $configurationService->getDefaultUpdateData();
            $data->setMode(3);
            $data->setNext(false);
            $configurationService->update($data);
            $currentMode = 3;
        }

        return $this->redirectToRoute(self::MODE_REDIRECTS[$currentMode]);
    }

    #[Route('/spotify', name: 'app_stage_spotify')]
    public function spotify(SpotifyAuthenticationService $spotifyAuthenticationService): Response
    {
        return $this->render('stage/spotify.html.twig', [
            'controller_name' => 'StageController',
            'token' => $spotifyAuthenticationService->getValidAccessToken(),
            'marginTop' => '40px',
        ]);
    }

    #[Route('/image', name: 'app_stage_image')]
    public function image(): Response
    {
        return $this->render('stage/image.html.twig', [
            'controller_name' => 'StageController',
            'token' => self::UNSPLASH_TOKEN
        ]);
    }

    #[Route('/greeting', name: 'app_stage_greeting')]
    public function greeting(GreetingSynchronizationService $greetingSynchronizationService, GreetingService $greetingService): Response
    {
        //synchronize images
        $greetingSynchronizationService->synchronizeGreetings();
        $greetings = $greetingService->getNewNonDisplayedGreetings();

        $url = 'test.de';
        if (count($greetings) > 0){
            $url = $greetings[0]->getCdnUrl();
            $greetingSynchronizationService->markAsDisplayed([$greetings[0]->getRemoteId()]);
            $data = (new GreetingData())->initFrom($greetings[0]);
            $data->setDisplayed(new DateTime());
            $greetingService->update($greetings[0], $data);
        }

        //todo get the oldest not shown image. If there are multiple:

        return $this->render('stage/greeting.html.twig', [
            'url' => $url
        ]);
    }
}
