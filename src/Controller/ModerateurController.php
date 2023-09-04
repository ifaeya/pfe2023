<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ModerateurController extends AbstractController
{
    #[Route('/moderateur', name: 'app_moderateur')]
    public function index(): Response
    {
        return $this->render('moderateur/index.html.twig', [
            'controller_name' => 'ModerateurController',
        ]);
    }
}
