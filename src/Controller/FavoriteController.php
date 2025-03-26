<?php

namespace App\Controller;

use App\Service\Spotify\SpotifyService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/favorite')]
final class FavoriteController extends AbstractController
{
    #[Route('/gallery', name: 'favorite_gallery')]
    public function login(): Response
    {
        return $this->render('favorite/gallery.html.twig');
    }
}
