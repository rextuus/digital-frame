<?php

namespace App\Controller;

use App\Entity\ArtsyImage;
use App\Service\Artsy\ArtsyService;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Greeting\Form\GreetingData;
use App\Service\Greeting\GreetingService;
use App\Service\Spotify\SpotifyService;
use App\Service\Synchronization\GreetingSynchronizationService;
use DateTime;
use SpotifyWebAPI\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stage')]
class StageController extends AbstractController
{
    public function __construct(
        #[Autowire(env: 'string:UNSPLASH_TOKEN')] private readonly string $unsplashToken
    ) {
    }

    #[Route('/', name: 'app_stage')]
    public function index(
        FrameConfigurationService $configurationService,
        GreetingSynchronizationService $greetingSynchronizationService
    ): Response {
        $currentMode = $configurationService->getMode();
        if ($greetingSynchronizationService->checkForNewGreetings()){
            $data = $configurationService->getDefaultUpdateData();
            $data->setMode(DisplayMode::GREETING);
            $data->setNext(false);
            $configurationService->update($data);
            $currentMode = DisplayMode::GREETING;
        }

        return $this->redirectToRoute($currentMode->getRedirect());
    }

    #[Route('/spotify', name: 'app_stage_spotify')]
    public function spotify(SpotifyService $spotifyService): Response
    {
        return $this->render('stage/spotify.html.twig', [
            'controller_name' => 'StageController',
            'token' => $spotifyService->getValidAccessToken()->getAccessToken(),
            'marginTop' => '40px',
        ]);
    }

    #[Route('/image', name: 'app_stage_unsplash')]
    public function image(): Response
    {
        return $this->render('stage/unsplash.html.twig', [
            'controller_name' => 'StageController',
            'token' => $this->unsplashToken
        ]);
    }

    #[Route('/artsy/{image<\d+>?}', name: 'app_stage_artsy')]
    public function artsy(
        ArtsyService $artsyService,
        FrameConfigurationService $configurationService,
        ?int $image = null
    ): Response {
        $nextImage = null;
        if ($image !== null){
            $nextImage = $artsyService->getArtworkById($image);
        }

        if ($nextImage === null){
            $nextImage = $artsyService->getCurrentArtwork();
        }
        $configurationService->setCurrentArtworkId($nextImage->getId());

        return $this->render('stage/artsy.html.twig', [
            'controller_name' => 'StageController',
            'image' => $nextImage,
        ]);
    }

    #[Route('/greeting', name: 'app_stage_greeting')]
    public function greeting(GreetingSynchronizationService $greetingSynchronizationService, GreetingService $greetingService): Response
    {
        //synchronize images
        $greetingSynchronizationService->synchronizeGreetingsFromServer();
        $greetings = $greetingService->getNewNonDisplayedGreetings();

        $url = 'test.de';
        if (count($greetings) > 0){
            $url = $greetings[0]->getCdnUrl();
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
