<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Entity\Caisse;
use App\Entity\Operation;
use App\Entity\Typeoperation;
use App\Entity\Mouvement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

class AlimentationController extends AbstractController
{
    #[Route('/transfert', name: 'app_transfert')]
    public function index(Request $request, ManagerRegistry $registry): Response
{
    $caisseRepository = $registry->getRepository(Caisse::class);
    $caisses = $caisseRepository->findAll();
    $libelleCaisseDepart = $request->request->get('caisse_depart');
    $libelleCaisseArrivee = $request->request->get('caisse_arrivee');
    $montantenvoye = $request->request->get('montant');
    $error = null;
    $success = null;

    if ($request->isMethod('POST')) {
        $caisseDepart = $caisseRepository->findOneBy(['libelle' => $libelleCaisseDepart]);
        $caisseArrivee = $caisseRepository->findOneBy(['libelle' => $libelleCaisseArrivee]);

        if ($caisseDepart && $caisseArrivee) {
            if ($caisseDepart->getSolde() >= $montantenvoye) {
                $caisseDepart->setSolde($caisseDepart->getSolde() - $montantenvoye);
                $caisseArrivee->setSolde($caisseArrivee->getSolde() + $montantenvoye);

                $entityManager = $registry->getManager();
                $entityManager->persist($caisseDepart);
                $entityManager->persist($caisseArrivee);
                
                // Sauvegarder les caisses mises à jour dans la base de données
                $entityManager->flush();
                $operation = new Operation();
                $typeOperation = new Typeoperation();
                $typeOperation->setLibelle('transfert');
                $operation->setTypeoperation($typeOperation);
    
                // Sauvegarder l'opération dans la base de données
                $em = $registry->getManager();
                $em->persist($operation);
                $em->flush();
                // Enregistrement du mouvement pour la caisse de départ
                $movementDepart = new Mouvement();
                $movementDepart->setSensMouvement('S');
                $movementDepart->setValeurDevise($montantenvoye);
                $movementDepart->setCaisse($caisseDepart);
                $movementDepart->setOperation($operation);
                $entityManager->persist($movementDepart);
                
                // Enregistrement du mouvement pour la caisse d'arrivée
                $movementArrivee = new Mouvement();
                $movementArrivee->setSensMouvement('E');
                $movementArrivee->setValeurDevise($montantenvoye);
                $movementArrivee->setCaisse($caisseArrivee);
                $movementArrivee->setOperation($operation);
                $entityManager->persist($movementArrivee);
                
                // Sauvegarder les mouvements dans la base de données
                $entityManager->flush();

                $success = 'Transfert effectué avec succès.';
            } else {
                $error = 'Fonds insuffisants dans la caisse de départ.';
            }
        } else {
            $error = 'Caisse de départ ou caisse d\'arrivée non trouvée.';
        }
    }

    return $this->render('transfert/index.html.twig', [
        'caisses' => $caisses,
        'error' => $error ?? null,
        'success' => $success ?? null,
        'libelleCaisseDepart' => $libelleCaisseDepart,
        'libelleCaisseArrivee' => $libelleCaisseArrivee,
        'montantenvoye' => $montantenvoye,
        
    ]);
}


#[Route('/alimentation', name: 'app_alimentation')]

public function index1(Request $request, ManagerRegistry $registry): Response
{
    $caisseRepository = $registry->getRepository(Caisse::class);
    $caisses = $caisseRepository->findAll();
    $libelleCaisse = $request->request->get('caisse');
    $montantAlimentation = $request->request->get('montant');
    $error = null;
    $success = null;

    if ($request->isMethod('POST')) {
        $caisse = $caisseRepository->findOneBy(['libelle' => $libelleCaisse]);

        if ($caisse) {
            // Mettre à jour le solde de la caisse
            $nouveauSolde = $caisse->getSolde() + $montantAlimentation;
            $caisse->setSolde($nouveauSolde);
            $operation = new Operation();
            $typeOperation = new Typeoperation();
            $typeOperation->setLibelle('Alimentation');
            $operation->setTypeoperation($typeOperation);

            // Sauvegarder l'opération dans la base de données
            $em = $registry->getManager();
            $em->persist($operation);
            $em->flush();
            // Créer un mouvement de réception d'argent pour l'alimentation de la caisse
            $mouvement = new Mouvement();
            $mouvement->setSensMouvement('E'); // Sens "Entrée"
            $mouvement->setValeurDevise($nouveauSolde); // Nouveau solde de la caisse
            $mouvement->setCaisse($caisse);
            $mouvement->setOperation($operation);
           
            // Enregistrement des modifications dans la base de données
            $em = $registry->getManager();
            $em->persist($caisse);
            $em->persist($mouvement);
            $em->flush();

            $success = 'Alimentation effectuée avec succès.';
        } else {
            $error = 'Caisse non trouvée.';
        }
    }

    return $this->render('alimentation/index.html.twig', [
        'caisses' => $caisses,
        'error' => $error ?? null,
        'success' => $success ?? null,
        'libelleCaisse' => $libelleCaisse,
        'montantAlimentation' => $montantAlimentation,
    ]);
}
/*public function index1(Request $request, ManagerRegistry $registry): Response
{
    $entityManager = $registry->getManager();

    if ($request->isMethod('POST')) {
        $name = $request->request->get('name');
        $quantity = (float) $request->request->get('quantity');
        $deviseId = (int) $request->request->get('devise');
        $caisseId = (int) $request->request->get('caisse');

        // Rechercher la devise et la caisse associées par leur ID
        $devise = $entityManager->getRepository(Devises::class)->find($deviseId);
        $caisse = $entityManager->getRepository(Caisse::class)->find($caisseId);

        if ($devise && $caisse) {
            // Créer un nouvel objet Stock
            $stock = new Stock();
            $stock->setName($name);
            $stock->setQuantity($quantity);
            $stock->setDevise($devise);
            $stock->setCaisse($caisse);

            // Enregistrer le nouvel objet Stock dans la base de données
            $entityManager->persist($stock);
            $entityManager->flush();

            // Afficher un message de succès
            $successMessage = 'Alimentation de la caisse effectuée avec succès.';
        } else {
            // Afficher un message d'erreur si la devise ou la caisse n'a pas été trouvée
            $errorMessage = 'Erreur : la devise ou la caisse n\'a pas été trouvée.';
        }
    }

    return $this->render('alimentation/index.html.twig', [
        'successMessage' => $successMessage ?? null,
        'errorMessage' => $errorMessage ?? null,
    ]);
}
*/
}
