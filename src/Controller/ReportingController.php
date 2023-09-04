<?php

namespace App\Controller;

use App\Entity\Devises;
use App\Entity\Typeoperation;
use App\Repository\CaisseRepository;
use App\Repository\ClientRepository;
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

class ReportingController extends AbstractController
{
    #[Route('/reporting', name: 'app_reporting')]
    public function index(
        ArchiveCoursdevisesRepository $archiveCoursdevisesRepository,
        CoursdevisesRepository $coursdevisesRepository,
        CaisseRepository $caisseRepository,
        OperationRepository $operationRepository,
        ClientRepository $clientRepository,
        DevisesRepository $devisesRepository,
    ): Response {


        //$pmpa = $operation->calculatePMPA();
        //  $pmpv = $operation->calculatePMPV();
        $operations = $operationRepository->findAll();
        // Compter les opérations d'achat
        $nombreOperationsAchat = $operationRepository->countOperationsByType('achat');

        // Compter les opérations de vente
        $nombreOperationsVente = $operationRepository->countOperationsByType('vente');

        // Récupérer les opérations d'achat et de vente
        $operationsAchat = $operationRepository->findByType('achat');
        $operationsVente = $operationRepository->findByType('vente');

        // ...

        // Préparez les données pour les graphiques (utilisez un format adapté pour Chart.js)
        $chartData = [
            'labels' => [], // Noms des opérations ou autres étiquettes
            'margeRealisee' => [],
            'resultatRealise' => [],
            'margeLatente' => [],
            // Ajoutez d'autres séries de données pour les autres statistiques
        ];

        foreach ($operations as $operation) {
            // Ajoutez les données de chaque opération aux séries de données
            $chartData['labels'][] = $operation->getId();
            $chartData['margeRealisee'][] = $operation->getMargeRealisee() ?? 0;
            $chartData['resultatRealise'][] = $operation->getResultatRealise() ?? 0;
            $chartData['margeLatente'][] = $operation->getMargeLatente() ?? 0;
            // Ajoutez les autres données ici...
        }

        // Convertissez les données en format JSON pour les transmettre au template
        $chartDataJson = json_encode($chartData);
        $pmpLabels = [];
        $pmpaData = [];
        $pmpvData = [];

        foreach ($operationsAchat as $operation) {
            // Calculez PMPA et PMPV pour chaque opération d'achat
            $pmpa = $operation->getPMPA(); // Méthode à implémenter dans l'entité Operation
            $pmpv = $operation->getPMPV(); // Méthode à implémenter dans l'entité Operation

            // Ajoutez les données au tableau pour le graphique
            $pmpLabels[] = 'Achat ID ' . $operation->getId();
            $pmpaData[] = $pmpa;
            $pmpvData[] = $pmpv;
        }

        foreach ($operationsVente as $operation) {
            // Calculez PMPA et PMPV pour chaque opération de vente
            $pmpa = $operation->getPMPA(); // Méthode à implémenter dans l'entité Operation
            $pmpv = $operation->getPMPV(); // Méthode à implémenter dans l'entité Operation

            // Ajoutez les données au tableau pour le graphique
            $pmpLabels[] = 'Vente ID ' . $operation->getId();
            $pmpaData[] = $pmpa;
            $pmpvData[] = $pmpv;
        }

        $operation = $operationRepository->findOneById(1); // Supposons que l'opération ait l'ID 1

        // Calculez les différentes statistiques à partir de l'opération
        $margeRealisee = $operation->getMargeRealisee();
        $resultatRealise = $operation->getResultatRealise();
        $margeLatente = $operation->getMargeLatente();
        $devisesVendues = $operation->getDevisesVendues();
        $positionVenteActuelle = $operation->getPositionVenteActuelle();
        $resultatLatent = $operation->getRsultatLatent();
        $pointMort = $operation->getPointMort();
        




        $archiveCoursdevises = $archiveCoursdevisesRepository->findAll();
        $nombreOperationsAchat = $operationRepository->countAchatOperationsToday();
        $nombreOperationsVente = $operationRepository->countVenteOperationsToday();


        // Compter les opérations d'achat
        $nombreOperationsAchat = $operationRepository->countOperationsByType('achat');

        // Compter les opérations de vente
        $nombreOperationsVente = $operationRepository->countOperationsByType('vente');

        // Retrieve all currency courses
        $coursdevises = $coursdevisesRepository->findByUpdatedAt();

        // Calculate statistics for profit of each currency
        $statistics = [];
        $profits = [];
        foreach ($coursdevises as $coursdevisesItem) {
            $currencyName = $coursdevisesItem->getDevises()->getCode();
            $profit = $coursdevisesItem->getMarge();
            $statistics[$currencyName] = [
                'profit' => $profit,
                'valeurachat' => $coursdevisesItem->getValeurachat(),
                'valeurvente' => $coursdevisesItem->getValeurvente(),
            ];

            // Store the profit for further calculations
            if ($profit !== null) {
                $profits[] = $profit;
            }
        }

        // Calculate other statistics
        $averageProfit = count($profits) > 0 ? array_sum($profits) / count($profits) : null;
        $highestProfit = count($profits) > 0 ? max($profits) : null;
        $lowestProfit = count($profits) > 0 ? min($profits) : null;


        /** @var User $user */
        $user = $this->getUser();
        return $this->render('reporting/index.html.twig', [
            'controller_name' => 'ReportingController',
            'margeRealisee' => $margeRealisee,
        'resultatRealise' => $resultatRealise,
        'margeLatente' => $margeLatente,
        'devisesVendues' => $devisesVendues,
        'positionVenteActuelle' => $positionVenteActuelle,
        'resultatLatent' => $resultatLatent,
        'pointMort' => $pointMort,
        'chartData' => $chartDataJson,

            'coursdevises' => $coursdevisesRepository->findByUpdatedAt(),
            'nombreOperationsAchat' => $nombreOperationsAchat,
    'nombreOperationsVente' => $nombreOperationsVente,
    'operation' => $operation,
    'pmpLabels' => $pmpLabels,
    'pmpaData' => $pmpaData,
    'pmpvData' => $pmpvData,


            'statistics' => $statistics,
            'averageProfit' => $averageProfit,
            'highestProfit' => $highestProfit,
            'lowestProfit' => $lowestProfit,
            'archiveCoursdevises' => $archiveCoursdevises,
        ]);
    }
}