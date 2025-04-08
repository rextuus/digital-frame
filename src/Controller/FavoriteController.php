<?php

namespace App\Controller;

use App\Service\Spotify\SpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[Route('/favorite')]
final class FavoriteController extends AbstractController
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    #[Route('/gallery', name: 'favorite_gallery')]
    public function login(): Response
    {
        return $this->render('favorite/gallery.html.twig');
    }
}
