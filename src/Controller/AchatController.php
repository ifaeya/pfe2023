<?php
// src/Controller/AchatController.php

namespace App\Controller;

use App\Entity\Caisse;
use App\Entity\Client;
use App\Entity\Devises;
use App\Entity\Document;
use App\Entity\Mouvement;
use App\Entity\Operation;
use App\Entity\Coursdevises;
use App\Entity\Typeoperation;
use App\Repository\DocumentRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class AchatController extends AbstractController
{
    
    #[Route('/achat', name: 'app_achat_index', methods: ['POST', 'GET'])]
    public function index(Request $request, ManagerRegistry $registry, ParameterBagInterface $parameterBag): Response
    {
         // Récupérer tous les clients depuis la base de données
         $clientRepository = $registry->getRepository(Client::class);
         $clients = $clientRepository->findAll();
        // Récupérer les données du formulaire client
        $selectedClientId = $request->request->get('clientSelect');
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $numcin = $request->request->get('numcin');
        $datecin = new \DateTime($request->request->get('dateCin'));
        $numpasseport = $request->request->get('numpasseport');
        $datepasseport = new \DateTime($request->request->get('datepasseport'));
        $datevalidpasport = new \DateTime($request->request->get('datevalidpasport'));
        $caisseRepository = $registry->getRepository(Caisse::class);
        $caisses = $caisseRepository->findBy(['fermee' => false]);
        $devisesRepository = $registry->getRepository(Devises::class);
        $devises = $devisesRepository->findBy(['code' => 'TND'], ['id' => 'ASC']);
        $allDevises = $devisesRepository->findAll();
        $devises = array_diff($allDevises, $devises);
        $code = $request->request->get('code');
        $montant = $request->request->get('montant');
        $convertedAmount = null;

        if ($request->isMethod('POST')) {
            $files = $request->files->get('files');
            $em = $registry->getManager();
            $devise = $registry->getRepository(Devises::class)->findOneBy(['code' => $code]);
            if ($devise && $devise->getDevisecours()->last()) {
                $valeurAchat = $devise->getDevisecours()->last()->getValeurachat();
                $convertedAmount = $valeurAchat * $montant;
            } else {
                // Handle the case when devisecours is empty or null
                $valeurAchat = 0;
                $convertedAmount = 0;
                // You can also provide an error message or log the issue here
            }
            // Vérifier si le client existe déjà dans la base de données
            $clientRepository = $registry->getRepository(Client::class);
            
            // Check if client exists by numcin or Numpasseport
            $client = $clientRepository->findOneBy(['numcin' => $numcin]) ?: $clientRepository->findOneBy(['numpasseport' => $numpasseport]);
            
            if ($selectedClientId) {
                // A client is selected from the list, so retrieve the client data from the database
                $clientRepository = $registry->getRepository(Client::class);
                $client = $clientRepository->find($selectedClientId);
    
                if (!$client) {
                    // Handle the case if the selected client does not exist (optional)
                    return new JsonResponse(['success' => false, 'message' => 'Selected client not found'], JsonResponse::HTTP_NOT_FOUND);
                }
            } else {
                // No client is selected from the list, so create a new client
                // Le client n'existe pas, créer un nouveau client
                if (!$client) {
                    $client = new Client();
                    $client->setNom($nom);
                    $client->setPrenom($prenom);
                    $client->setnumcin($numcin);
                    $client->setDatecin($datecin);
                    $client->setNumpasseport($numpasseport);
                    $client->setDatepasseport($datepasseport);
                    $client->setDatevalidpasport($datevalidpasport);
    
                    // Sauvegarder le client dans la base de données
                    $em = $registry->getManager();
                    $em->persist($client);
                    $em->flush();
                }
            }
          
            $operation = new Operation(); // Créez une nouvelle instance de l'entité Operation
            $typeOperation = new Typeoperation(); // Créez une nouvelle instance de l'entité Typeoperation
            $typeOperation->setLibelle('achat'); // Configurez le libellé du type d'opération
            $operation->setTypeoperation($typeOperation); // Associez le type d'opération à l'opération
            
            if ($files) {
                $targetDirectory = $parameterBag->get('upload_directory_fichiers'); 
            
                foreach ($files as $file) {
                    if ($file instanceof UploadedFile) {
                        $fileName = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
                        $file->move($targetDirectory, $fileName);
            
                        $document = new Document();
                        $document->setLibelle($fileName);
            
                        $operation->addDocument($document);
            
                        $em->persist($document);
                    }
                }
            
                $em->flush();
            }
            
            $operation->setClient($client); // Associez le client à l'opération
            
            $em = $registry->getManager(); // Obtenez l'EntityManager
            
            $em->persist($operation); // Persistez l'opération
            $em->flush(); // Enregistrez les changements dans la base de données
            
            $movement = new Mouvement(); // Créez une nouvelle instance de l'entité Mouvement
            $movement->setSensMouvement('Entrée'); // Configurez le sens du mouvement
            $caisse = $devise->getCaisse()->first(); // Obtenez la première caisse associée à la devise
            $caisseSolde = $caisse->getSolde(); // Obtenez le solde de la caisse

            if ($caisseSolde < $montant) {
                return $this->render('achat/index.html.twig', [
                    'devises' => $devises,
                    'error' => 'Le montant de la caisse est insuffisant',
                    'code' => $code,
                    'convertedAmount' => null,
                    'caisses' => $caisses,
                    'clients' => $clients,
                    'client' => $client, // Ajoutez cette ligne pour passer les informations du client à la vue

                ]);
            } else {
                // Ajouter une logique pour choisir la caisse
                $selectedCaisseId = $request->request->get('caisse'); // Supposons que le formulaire ait un champ 'caisse' pour sélectionner la caisse
                $selectedCaisse = $registry->getRepository(Caisse::class)->find($selectedCaisseId);

                if (!$selectedCaisse || $selectedCaisse->Fermee()) {
                    return $this->render('achat/index.html.twig', [
                        'devises' => $devises,
                        'error' => 'La caisse sélectionnée n\'existe pas',
                        'code' => $code,
                        'convertedAmount' => null,
                        'caisses' => $caisseRepository->findBy(['fermee' => false]),
                        'clients' => $clients,
                        'client' => $client, // Ajoutez cette ligne pour passer les informations du client à la vue

                    ]);
                }

                $selectedCaisseSolde = $selectedCaisse->getSolde();

                if ($selectedCaisseSolde < $montant) {
                    return $this->render('achat/index.html.twig', [
                        'devises' => $devises,
                        'error' => 'Le montant de la caisse sélectionnée est insuffisant',
                        'code' => $code,
                        'convertedAmount' => null,
                        'caisses' => $caisseRepository->findBy(['fermee' => false]),
                        'clients' => $clients,
                        'client' => $client, // Ajoutez cette ligne pour passer les informations du client à la vue

                    ]);
                }

                $selectedCaisse->setSolde($selectedCaisseSolde + $montant);
                $movement->setValeurDevise($montant);
                $movement->setOperation($operation);
                $movement->setDevise($devise);
                $movement->setCaisse($selectedCaisse);
                $em = $registry->getManager();
                $em->persist($movement);
                $em->persist($selectedCaisse);
                $em->flush();

                $movement = new Mouvement();
                $movement->setSensMouvement('Sortie');
                $tndDevise = $registry->getRepository(Devises::class)->findOneBy(['code' => 'TND']);
                $tndCaisse = $tndDevise->getCaisse()->first();
                $caisseSolde = $tndCaisse->getSolde();
                $tndCaisse->setSolde($caisseSolde - $convertedAmount);
                $movement->setValeurDevise($convertedAmount);
                $movement->setOperation($operation);
                $movement->setDevise($tndDevise);
                $movement->setCaisse($tndCaisse);
                $em = $registry->getManager();
                $em->persist($movement);
                $em->persist($tndCaisse);
                $em->flush();
            }

            // Récupérer les détails de l'achat
            $nomClient = $client->getNom();
            $prenom = $client->getPrenom(); // Check if getPrenom() returns the correct value
            $numcin = $client->getNumCin();
            $numpasseport = $client->getNumPasseport();
            $deviseAchat = $devise->getLibelle(); // Remplacez "getNom()" par la méthode appropriée pour obtenir le nom de la devise
            $montantAchat = $montant;
            $montantConverti = $convertedAmount;

            // Determine which identifier to display
            $identifier = $numcin ? $numcin : $numpasseport;
            // Afficher les détails de l'achat
            return $this->render('achat/details.html.twig', [
                'nomClient' => $nomClient,
                'prenom' => $prenom, // Make sure $prenom is correctly defined
                'identifier' => $identifier,
                'numpasseport' => $numpasseport,
                'valeurAchat' => $valeurAchat,
                'numcin' => $numcin,
                'deviseAchat' => $deviseAchat,
                'montantAchat' => $montantAchat,
                'montantConverti' => $montantConverti,
                'clients' => $clients,
                'client' => $client, // Ajoutez cette ligne pour passer les informations du client à la vue

            ]);
        }

        // Filter caisses based on the selected devise's code
        $selectedDevise = $registry->getRepository(Devises::class)->findOneBy(['code' => $code]);

        if ($selectedDevise) {
            $filteredCaisses = array_filter($caisses, function ($caisse) use ($selectedDevise) {
                return $caisse->getDevises()->getCode() === $selectedDevise->getCode();
            });
        } else {
            // Gérer le cas où la devise sélectionnée n'a pas été trouvée
            // Peut-être afficher un message d'erreur ou prendre une autre action appropriée
            $filteredCaisses = [];
        }
        
        
        return $this->render('achat/index.html.twig', [
            'devises' => $devises,
            'convertedAmount' => $convertedAmount,
            'error' => null,
            'success' => null,
            'code' => $code,
            'caisses' => $filteredCaisses,
            'caisses' => $caisses,
            'clients' => $clients,
        ]);
    }


    
    #[Route('/achat/client/{id}', name: 'app_achat_get_client', methods: ['GET'])]
    public function getClient(Request $request, ManagerRegistry $registry, $id): JsonResponse
    {
        $clientRepository = $registry->getRepository(Client::class);
        $client = $clientRepository->find($id);
    
        if (!$client) {
            return new JsonResponse(['success' => false, 'message' => 'Client not found'], JsonResponse::HTTP_NOT_FOUND);
        }
    
        // Return the client's data as JSON
        return new JsonResponse([
            'success' => true,
            'client' => [
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
                'numcin' => $client->getnumcin(),
                'datecin' => $client->getDateCin()->format('Y-m-d'), // Format the date as 'Y-m-d'
                'numpasseport' => $client->getNumpasseport(),
                'datepasseport' => $client->getDatepasseport()->format('Y-m-d'), // Format the date as 'Y-m-d'
                'datevalidpasport' => $client->getDatevalidpasport()->format('Y-m-d'), // Format the date as 'Y-m-d'
            ],
        ]);
    }
    #[Route('/get_caisse_solde/{id}', name: 'get_caisse_solde', methods: ['GET'])]
    public function getCaisseSolde(Caisse $caisse): JsonResponse
    {
        // Pas besoin de rechercher la caisse à nouveau, elle est déjà passée en tant que paramètre
        $solde = $caisse->getSolde();
    
        return new JsonResponse(['solde' => $solde]);
    }
 /*   #[Route('/achat/annuler/{id}', name: 'app_achat_annuler', methods: ['GET'])]
    public function annulerOperation(Request $request, ManagerRegistry $registry, $id): Response
    {
        $operationRepository = $registry->getRepository(Operation::class);
        $operation = $operationRepository->find($id);

        if (!$operation) {
            $this->addFlash('error', 'L\'opération que vous essayez d\'annuler n\'a pas été trouvée.');
            return $this->redirectToRoute('app_operation_index');
        }

        // Restaurer le solde de la caisse associée à l'opération
        $movementRepository = $registry->getRepository(Mouvement::class);
        $movements = $movementRepository->findBy(['operation' => $operation]);

        foreach ($movements as $movement) {
            $caisse = $movement->getCaisse();
            $montantRestitue = $movement->getValeurDevise();

            $caisse->setSolde($caisse->getSolde() + $montantRestitue);

            $em = $registry->getManager();
            $em->persist($caisse);
            $em->remove($movement); 
        }

       
        $operation->setStatus('Annulée');

      
        $em = $registry->getManager();
        $em->persist($operation);
        $em->flush();

     
        $this->addFlash('success', 'L\'opération a été annulée avec succès.');
        return $this->redirectToRoute('app_operation_index'); 
    }*/
    #[Route('/achat/annuler/{id}', name: 'app_achat_annuler', methods: ['GET'])]
    public function annulerOperation(Request $request, ManagerRegistry $registry, $id): Response
    {
        $operationRepository = $registry->getRepository(Operation::class);
        $operation = $operationRepository->find($id);
    
        if (!$operation) {
            $this->addFlash('error', 'L\'opération que vous essayez d\'annuler n\'a pas été trouvée.');
            return $this->redirectToRoute('app_operation_index');
        }
    
        $entityManager = $registry->getManager();
    
        // Restaurer le solde de la caisse associée à l'opération
        $movementRepository = $registry->getRepository(Mouvement::class);
        $movements = $movementRepository->findBy(['operation' => $operation]);
    
        foreach ($movements as $movement) {
            $caisse = $movement->getCaisse();
            $montantRestitue = $movement->getValeurDevise();
    
            if ($movement->getSensMouvement() === 'E') {
                // Si le mouvement est de type 'E' (entrée), soustrayez le montant pour l'annulation
                $caisse->setSolde($caisse->getSolde() - $montantRestitue);
            } else {
                // Si le mouvement est de type 'S' (sortie), ajoutez le montant pour l'annulation
                $caisse->setSolde($caisse->getSolde() + $montantRestitue);
            }
    
            $entityManager->persist($caisse);
        }
    
        // Mettre à jour le statut de l'opération à "Annulée"
        $operation->setStatus('Annulée');
    
        // Persist the updated operation with the new status
        $entityManager->persist($operation);
        $entityManager->flush();
    
        // Rediriger ou afficher un message de succès
        $this->addFlash('success', 'L\'opération a été annulée avec succès.');
        return $this->redirectToRoute('app_operation_index'); // Rediriger vers la page d'achat par exemple
    }
    
    #[Route('/fichiers/{id}', name: 'app_download_document')]
    public function downloadDocument($id, DocumentRepository $documentRepository)
    {
        $document = $documentRepository->find($id);
    
        if (!$document) {
            throw $this->createNotFoundException('Document not found');
        }
    
        $filePath = $this->getParameter('upload_directory_fichiers') . $document->getLibelle();
    
        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $document->getLibelle());
    
        return $response;
    }
    








    #[Route('/achat/edit/{id}', name: 'app_achat_edit', methods: ['GET', 'POST'])]
    public function editAchat(Request $request, ManagerRegistry $registry, $id): Response
    {
        $devisesRepository = $registry->getRepository(Devises::class);
        $devises = $devisesRepository->findAll();

        $operationRepository = $registry->getRepository(Operation::class);
        $operation = $operationRepository->find($id);

        $caisseRepository = $registry->getRepository(Caisse::class);
        $caisses = $caisseRepository->findAll();
        $convertedAmount = null;
        if (!$operation) {
            $this->addFlash('error', 'L achat que vous essayez de modifier n\'a pas été trouvée.');
            return $this->redirectToRoute('app_achat_index');
        }
        $montantDiff = null;
        if ($request->isMethod('POST')) {
            $newData = $request->request->all();

           
            $operation->setNom($newData['nom']);
            $operation->setPrenom($newData['prenom']);
            $operation->setNumcin($newData['numcin']);
            $operation->setDateCin(new \DateTime($newData['dateCin']));
            $operation->setNumPasseport($newData['numpasseport']);
            $operation->setDatePasseport(new \DateTime($newData['datepasseport']));
            $operation->setDatevalidpasport(new \DateTime($newData['datevalidpasport']));

            $em = $registry->getManager();
            $em->persist($operation);
            $em->flush();

           
            $caisses = $operation->getCaisse(); 
            $devises = $operation->getDevise(); 
            $convertedAmount = $operation->getMontant() * $operation->getValeurAchat();
            $montantDiff = $convertedAmount;
          

            $this->addFlash('success', 'La achat a été mise à jour avec succès.');
            return $this->render('achat/edit.html.twig', [
                'operation' => $operation,
                'devises' => $devises,
                'caisses' => $caisses,
                'convertedAmount' => $convertedAmount, // Ajouter la variable montantDiff à la vue
            ]);
        }
    
        return $this->render('achat/edit.html.twig', [
            'operation' => $operation,
            'devises' => $devises,
            'caisses' => $caisses,
            'convertedAmount' => $convertedAmount,
        ]);
    }


    #[Route('/imprimer', name: 'app_imprimer')]
    public function imprimer(): Response
    {
        return $this->render('achat/imprimer.html.twig', [
          
        ]);
    }
    #[Route('/imprimer2', name: 'app_imprimer2')]
    public function imprimer2(): Response
    {
        return $this->render('achat/imprimer2.html.twig', [
          
        ]);
    }
}
