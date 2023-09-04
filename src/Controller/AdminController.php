<?php

namespace App\Controller;
use App\Entity\Caisse;
use DateTime;
use App\Form\CoursdevisesType;
use App\Repository\CoursdevisesRepository;
use App\Repository\ArchiveCoursdevisesRepository;
use App\Entity\User;
use App\Repository\CaisseRepository;
use App\Repository\OperationRepository;
use App\Repository\ClientRepository;
use App\Repository\DevisesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]

    public function index(CoursdevisesRepository $coursdevisesRepository , CaisseRepository $caisseRepository,OperationRepository $operationRepository, ClientRepository $clientRepository, DevisesRepository $devisesRepository) : Response
    
    {
        

        $nombreOperationsAchat = $operationRepository->countOperationsByType('achat');
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
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
            'coursdevises' => $coursdevisesRepository->findByUpdatedAt(),
            'nombreOperationsAchat' => $nombreOperationsAchat,
            'nombreOperationsVente' => $nombreOperationsVente,
            'statistics' => $statistics,
            'averageProfit' => $averageProfit,
            'highestProfit' => $highestProfit,
            'lowestProfit' => $lowestProfit,
        ]);
    }
    
    #[Route('/historical/{date}', name: 'app_historical_data')]
    public function historicalDataForDate(string $date, CoursdevisesRepository $coursdevisesRepository, ArchiveCoursdevisesRepository $archiveCoursdevisesRepository): Response
    {
        // Convert the string date to a DateTime object
        $dateTime = DateTime::createFromFormat('Y-m-d', $date);

        // Fetch historical data for the specific date
        $historicalData = $archiveCoursdevisesRepository->findByCreatedAt($dateTime);

        return $this->render('admin1/historical_data.html.twig', [
            'historicalData' => $historicalData,
            // You can add any other data you want to pass to the template
        ]);
    }

    
/**
 * @Route("/caisse/toggle/{id}", name="caisse_toggle")
 */
public function toggleCaisse(Caisse $caisse, ManagerRegistry $registry): Response
{
    $caisse->setFermee(!$caisse->Fermee()); // inverse l'état de la caisse
    $em = $registry->getManager();
    $em->flush();

    return $this->redirectToRoute('app_caisse_index');
}
/**
 * @Route("/caisse_close_all", name="app_caisse_close_all")
 * @param CaisseRepository $repository
 */
public function closeAll(CaisseRepository $repository, ManagerRegistry $managerRegistry): RedirectResponse
{
    $caisses = $repository->findAll();
    $entityManager = $managerRegistry->getManager();
    foreach ($caisses as $caisse) {
        $caisse->setFermee(true);
        $entityManager->persist($caisse);
    }
    $entityManager->flush();

    $this->addFlash('success', 'Toutes les caisses ont été fermées avec succès.');

    return $this->redirectToRoute('app_admin');
}

/**
 * @Route("/caisse_open_all", name="app_caisse_open_all")
 *
 */
public function openAll(CaisseRepository $repository, ManagerRegistry $managerRegistry): RedirectResponse
{
    $caisses = $repository->findAll();
    foreach ($caisses as $caisse) {
        $caisse->setFermee(false);
        $entityManager = $managerRegistry->getManager();
        $entityManager->persist($caisse);
    }
    $entityManager->flush();

    $this->addFlash('success', 'Toutes les caisses ont été ouvertes avec succès.');

    return $this->redirectToRoute('app_caisse_index');
}

}



