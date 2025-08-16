<?php

namespace App\Controller;

use App\Repository\FavoriteRepository;
use App\Service\FrameConfiguration\DisplayMode;
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
    #[Route('/gallery', name: 'favorite_gallery')]
    public function gallery(Request $request, FavoriteRepository $favoriteRepository
    ): Response {
        $modesJson = $request->query->get('modes', '{}');
        $modes = json_decode($modesJson, true) ?: [];

        // available
        if ($modes === []){
            $availableModes = $favoriteRepository->getPresentModes();
            $availableModes = array_map(fn(array $mode) => $mode[array_key_first($mode)]->name, $availableModes);
            foreach (DisplayMode::cases() as $mode) {
                $modes[$mode->name] = true;
                if (!in_array($mode->name, $availableModes)) {
                    $modes[$mode->name] = false;
                }
            }
        }

        $toggleMode = $request->query->get('toggleMode');
        if ($toggleMode && array_key_exists($toggleMode, $modes)) {
            $modes[$toggleMode] = !$modes[$toggleMode]; // Toggle the mode
        }


        return $this->render('favorite/gallery.html.twig', [
            'modesEnabled' => $modes,
        ]);
    }
}
