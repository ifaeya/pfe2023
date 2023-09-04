<?php

namespace App\Controller;

use App\Entity\Devises;
use App\Entity\Typeoperation;
use App\Repository\DevisesRepository;
use App\Repository\OperationRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CoursdevisesRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArchiveCoursdevisesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccueilController extends AbstractController
{
    #[Route('/', name: 'app_accueil')]
    public function index(Request $request, ManagerRegistry $registry,OperationRepository $operationRepository, DevisesRepository $devisesRepository , ArchiveCoursdevisesRepository $archiveCoursdevisesRepository, CoursdevisesRepository $coursdevisesRepository): Response
    {
           // Retrieve all currency courses
       $coursdevises = $coursdevisesRepository->findByUpdatedAt();
        $devisesRepository = $registry->getRepository(Devises::class);
        $devises =  $devisesRepository->findAll();
        $TypeoperationRepository = $registry->getRepository(Typeoperation::class);
        $typeoperations =  $TypeoperationRepository->findAll();
        $code = $request->request->get('code');
        $montant = $request->request->get('montant');
        $convertedAmount = null;
        $typeoperation = $request->request->get('libelle'); // Ajoutez cette ligne pour récupérer la valeur de "operation" depuis la requête

        if ($request->isMethod('POST')) {
            $devise = $registry->getRepository(Devises::class)->findOneBy(['code' => $code]);
            $montant = $request->request->get('montant');
            $typeoperation = $request->request->get('libelle');

            $devise = $registry->getRepository(Devises::class)->findOneBy(['code' => $code]);
            if (!$devise) {
                $this->addFlash('error', 'Currency not found.');
                return $this->redirectToRoute('app_accueil');
            }

            $valeurAchat = $devise->getDevisecours()->last()->getValeurachat();
            $valeurVente = $devise->getDevisecours()->last()->getValeurVente();

            // Corrected the condition here.
            if ($typeoperation === "achat") {
                $convertedAmount = $valeurAchat * $montant;
            } else {
                $convertedAmount = $valeurVente * $montant;
            }

            return $this->render('accueil/index.html.twig', [
                'devises' => $devises,
                'devise' => $devise,
                'typeoperations' => $typeoperations,
                'code' => $code,
                'montant' => $montant,
                'typeoperation' => $typeoperation,
                'valeurAchat' => $valeurAchat,
                'valeurVente' => $valeurVente,
                'convertedAmount' => $convertedAmount,
            ]);
        }

        // Render the initial form for currency conversion.
        return $this->render('accueil/index.html.twig', [
            'devises' => $devises,
            'typeoperations' => $typeoperations,
            'coursdevises' => $coursdevisesRepository->findByUpdatedAt(),
            'code' => $code,
            'montant' => $montant,
            'typeoperation' => $typeoperation,

            'convertedAmount' => $convertedAmount,
        ]);
    }


    #[Route('/convert_currency', name: 'convert_currency', methods: ['POST'])]
    public function convertCurrency(Request $request, ManagerRegistry $registry)
    {
        $code = $request->request->get('code');
        $montant = $request->request->get('montant');
        $typeoperation = $request->request->get('typeoperation');
        $em = $registry->getManager();
        $devise = $em->getRepository(Devises::class)->findOneBy(['code' => $code]);
        if (!$devise) {
            return new JsonResponse(['error' => 'Currency not found.'], 400);
        }
        $cours = $typeoperation === 'Vente' ? $devise->getDevisecours()->last()->getValeurVente() : $devise->getDevisecours()->last()->getValeurachat();
        $convertedAmount = $cours * $montant;
        return new JsonResponse(['convertedAmount' => $convertedAmount, 'currencyCode' => $devise->getCode()]);
    }
}
