<?php

namespace App\Controller;
use DateTime;
use App\Entity\User;
use App\Entity\Caisse;
use GuzzleHttp\Client;
use App\Entity\Operation;
use App\Form\CoursdevisesType;
use App\Repository\CaisseRepository;
use App\Repository\ClientRepository;
use App\Repository\DevisesRepository;
use App\Repository\OperationRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\CoursdevisesRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArchiveCoursdevisesRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/home')]
class HomeController extends AbstractController
{ private $entityManager;
    public function __construct(ManagerRegistry $registry)
    {
        $this->entityManager = $registry->getManager();
    }
    #[Route('/', name: 'app_home')]
    public function index(  ArchiveCoursdevisesRepository $archiveCoursdevisesRepository,
    CoursdevisesRepository $coursdevisesRepository,
    CaisseRepository $caisseRepository,
    OperationRepository $operationRepository,
    ClientRepository $clientRepository,
    DevisesRepository $devisesRepository,
    ) : Response
    
    {


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
        return $this->render('admin1/home.html.twig', [
            'controller_name' => 'HomeController',
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

    public function convertCurrency($amount, $from_currency, $to_currency) {
        // Récupération du taux de change depuis l'API de conversion de devises
        $url = "https://api.exchangerate-api.com/v4/latest/$from_currency";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $exchange_rate = $data['rates'];

        // Calcul de la conversion pour chaque devise cible
        $converted_amounts = array();
        foreach($to_currency as $currency) {
            if(array_key_exists($currency, $exchange_rate)) {
                $converted_amount = $amount * $exchange_rate[$currency];
                $converted_amounts[$currency] = round($converted_amount, 2);
            }
        }
        return $converted_amounts;
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

    return $this->redirectToRoute('app_home');
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



