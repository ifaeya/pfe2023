<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\OperationRepository;

class HistoriqueController extends AbstractController
{
    #[Route('/historique', name: 'app_historique')]
    public function index(OperationRepository $operationRepository): Response
    {
        $operations = $operationRepository->findAllSortedByDateDesc();

        return $this->render('historique/index.html.twig', [
            'operations' => $operations,
        ]);
    }

}
