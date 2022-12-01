<?php

namespace App\Controller;

use ColorThief\ColorThief;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    private int $mode = 0;

    #[Route('/configuration/change', name: 'app_configuration_change')]
    public function change(Request $request): Response
    {
        $mode = $request->query->get('mode');
        if ($mode){
            $this->mode = (int) $mode;
        }
        return new JsonResponse(['mode' => $this->mode]);
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
