<?php

namespace App\Controller;

use App\Service\Spotify\SpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/spotify')]
final class SpotifyController extends AbstractController
{
    public function __construct(
        private readonly SpotifyService $spotifyService
    ) {
    }

    #[Route('/login', name: 'spotify_login')]
    public function login(): RedirectResponse
    {
        // Generate the Spotify login URL
        $loginUrl = $this->spotifyService->getOauthToken();

        // Redirect the user to the Spotify login page
        return new RedirectResponse($loginUrl);
    }

    #[Route('/callback', name: 'spotify_callback')]
    public function callback(Request $request): Response
    {
        $error = $request->query->get('error');
        if ($error) {
            return new Response("Error: " . $error);
        }

        $authorizationCode = $request->query->get('code');
        $state = $request->query->get('state');

        if ($authorizationCode && $state) {
            // Exchange the authorization code for an access token
            $this->spotifyService->createAccessToken($authorizationCode);

            return $this->redirectToRoute('app_configuration_landing');
        }

        return new Response("Authorization failed.");
    }

    #[Route('/info/', name: 'spotify_info')]
    public function info(): Response
    {
        try {
            $displayName = $this->spotifyService->getLoginInformation();
        } catch (\Exception  $e) {
            $displayName = 'not_logged_in';
        }

        return $this->render('spotify/info.html.twig', [
            'displayName' => $displayName,
        ]);
    }
}
