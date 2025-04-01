<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stage')]
class StageController extends AbstractController
{
    #[Route('/', name: 'app_stage_show')]
    public function stage(): Response
    {
        return $this->render('stage/show.html.twig');
    }
}
