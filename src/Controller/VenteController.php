<?php

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

class VenteController extends AbstractController
{
    #[Route('/vente', name: 'app_vente_index', methods: ['POST', 'GET'])]
    public function index(Request $request, ManagerRegistry $registry, ParameterBagInterface $parameterBag): Response
    {
        // Récupérer tous les clients depuis la base de données
        $clientRepository = $registry->getRepository(Client::class);
        $clients = $clientRepository->findAll();

        // Récupérer les données du formulaire client
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
            $valeurVente = $devise->getDevisecours()->last()->getValeurvente();
            $convertedAmount = $montant * $valeurVente;
            if ($devise && $devise->getDevisecours()->last()) {
                $ValeurVente = $devise->getDevisecours()->last()->getValeurachat();
                $convertedAmount = $ValeurVente * $montant;
            } else {
                // Handle the case when devisecours is empty or null
                $ValeurVente = 0;
                $convertedAmount = 0;
                // You can also provide an error message or log the issue here
            }

            // Vérifier si le client existe déjà dans la base de données
            $client = $clientRepository->findOneBy(['numcin' => $numcin]);

            if (!$client) {
                // Le client n'existe pas, créer un nouveau client
                $client = new Client();
                $client->setNom($nom);
                $client->setPrenom($prenom);
                $client->setNumcin($numcin);
                $client->setDateCin($datecin);
                $client->setNumPasseport($numpasseport);
                $client->setDatePasseport($datepasseport);
                $client->setDatevalidpasport($datevalidpasport);

                // Sauvegarder le client dans la base de données
                $em = $registry->getManager();
                $em->persist($client);
                $em->flush();
            }

            // Créer une nouvelle instance de l'entité Operation et assigner le client et le type d'opération
            $operation = new Operation();
            $operation->setClient($client);

            $typeOperation = new Typeoperation();
            $typeOperation->setLibelle('vente');
            $operation->setTypeoperation($typeOperation);
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
            
            
            // Sauvegarder l'opération dans la base de données
            $em = $registry->getManager();
            $em->persist($operation);
            $em->flush();

            // Créer une nouvelle instance de l'entité Mouvement et assigner les valeurs
            $movement = new Mouvement();
            $movement->setSensMouvement('S');
            $caisse = $devise->getCaisse()[0];
            $caisseSolde = $caisse->getSolde();

            if ($caisseSolde < $convertedAmount) {
                return $this->render('vente/index.html.twig', [
                    'devises' => $devises,
                    'error' => 'Le montant de la caisse est insuffisant',
                    'code' => $code,
                    'convertedAmount' => null,
                    'caisses' => $caisseRepository->findBy(['fermee' => false]),
                    'clients' => $clients,
                ]);
            } else {
                // Ajouter une logique pour choisir la caisse
                $selectedCaisseId = $request->request->get('caisse'); // Supposons que le formulaire ait un champ 'caisse' pour sélectionner la caisse
                $selectedCaisse = $registry->getRepository(Caisse::class)->find($selectedCaisseId);

                if (!$selectedCaisse || $selectedCaisse->Fermee()) {
                    return $this->render('vente/index.html.twig', [
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
                    return $this->render('vente/index.html.twig', [
                        'devises' => $devises,
                        'error' => 'Le montant de la caisse sélectionnée est insuffisant',
                        'code' => $code,
                        'convertedAmount' => null,
                        'caisses' => $caisseRepository->findBy(['fermee' => false]),
                        'clients' => $clients,
                        'client' => $client, // Ajoutez cette ligne pour passer les informations du client à la vue
                    ]);
                }

                $caisse->setSolde($caisseSolde - $convertedAmount);
                $movement->setValeurDevise($convertedAmount);
                $movement->setOperation($operation);
                $movement->setDevise($devise);
                $movement->setCaisse($caisse);
                $em->persist($movement);
                $em->persist($caisse);
                $em->flush();

                $movement = new Mouvement();
                $movement->setSensMouvement('E');
                $tndDevise = $registry->getRepository(Devises::class)->findOneBy(['code' => 'TND']);
                $tndCaisse = $tndDevise->getCaisse()[0];
                $caisseSolde = $tndCaisse->getSolde();
                $tndCaisse->setSolde($caisseSolde + $montant);
                $movement->setValeurDevise($montant);
                $movement->setOperation($operation);
                $movement->setDevise($tndDevise);
                $movement->setCaisse($tndCaisse);
                $em->persist($movement);
                $em->persist($tndCaisse);
                $em->flush();
            }

            // Récupérer les détails de la vente
            $nomClient = $client->getNom();
            $prenom = $client->getPrenom(); // Check if getPrenom() returns the correct value
            $numcin = $client->getNumcin();
            $numpasseport = $client->getNumPasseport();
            $deviseVente = $devise->getLibelle();
            $montantVente = $montant;
            $montantConverti = $convertedAmount;
            // Determine which identifier to display
            $identifier = $numcin ? $numcin : $numpasseport;
            // Afficher les détails de la vente
            return $this->render('vente/details.html.twig', [
                'valeurVente' => $valeurVente,
                'devises' => $devises,
                'convertedAmount' => $convertedAmount,
                'identifier' => $identifier,
                'numpasseport' => $numpasseport,
                'montantConverti' => $montantConverti,
                'code' => $code,
                'caisses' => $caisses,
                'clients' => $clients,
                'nomClient' => $nomClient,
                'prenom' => $prenom, // Make sure $prenom is correctly defined
                'numcin' => $numcin,
                'deviseVente' => $deviseVente,
                'montantVente' => $montantVente,
                'montantConverti' => $convertedAmount,
            ]);
        }

        return $this->render('vente/index.html.twig', [
            'devises' => $devises,
            'convertedAmount' => $convertedAmount,
            'code' => $code,
            'caisses' => $caisses,
            'clients' => $clients,
        ]);
    }

    #[Route('/vente/client/{id}', name: 'app_vente_get_client', methods: ['GET'])]
    public function getClient(Request $request, ManagerRegistry $registry, $id): JsonResponse
    {
        $clientRepository = $registry->getRepository(Client::class);
        $client = $clientRepository->find($id);

        if (!$client) {
            return new JsonResponse(['success' => false, 'message' => 'Client not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Renvoyer les informations du client en tant que réponse JSON
        return new JsonResponse([
            'success' => true,
            'client' => [
                'nom' => $client->getNom(),
                'prenom' => $client->getPrenom(),
                'numcin' => $client->getNumcin(),
                'dateCin' => $client->getDateCin()->format('Y-m-d'),
                'numpasseport' => $client->getNumPasseport(),
                'datepasseport' => $client->getDatePasseport()->format('Y-m-d'),
                'datevalidpasport' => $client->getDateValidPasseport()->format('Y-m-d'),
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






    #[Route('/vente/annuler/{id}', name: 'app_vente_annuler', methods: ['GET'])]
    public function annulerVente(Request $request, ManagerRegistry $registry, $id): Response
    {
        $operationRepository = $registry->getRepository(Operation::class);
        $operation = $operationRepository->find($id);
        $caisseRepository = $registry->getRepository(Caisse::class);
        $caisses = $caisseRepository->findBy(['fermee' => false]);

        if (!$operation) {
            $this->addFlash('error', 'La vente que vous essayez d\'annuler n\'a pas été trouvée.');
            return $this->redirectToRoute('app_operation_index');
        }

        $entityManager = $registry->getManager();

        // Restaurer le solde de la caisse associée à l'opération de vente
        $movementRepository = $registry->getRepository(Mouvement::class);
        $movements = $movementRepository->findBy(['operation' => $operation]);

        foreach ($movements as $movement) {
            $caisse = $movement->getCaisse();
            $montantRestitue = $movement->getValeurDevise();

            if ($movement->getSensMouvement() === 'S') {
                // Si le mouvement est de type 'S' (sortie), soustrayez le montant pour l'annulation
                $caisse->setSolde($caisse->getSolde() - $montantRestitue);
            } else {
                // Si le mouvement est de type 'E' (entrée), ajoutez le montant pour l'annulation
                $caisse->setSolde($caisse->getSolde() + $montantRestitue);
            }

            $entityManager->persist($caisse);
        }

       
        $operation->setStatus('Annulée');

     
        $entityManager->persist($operation);
        $entityManager->flush();

       
        $this->addFlash('success', 'La vente a été annulée avec succès.');
        return $this->redirectToRoute('app_operation_index');
    }






    #[Route('/vente/edit/{id}', name: 'app_vente_edit', methods: ['GET', 'POST'])]
    public function editVente(Request $request, ManagerRegistry $registry, $id): Response
    {
        $devisesRepository = $registry->getRepository(Devises::class);
        $devises = $devisesRepository->findAll();

        $operationRepository = $registry->getRepository(Operation::class);
        $operation = $operationRepository->find($id);

        $caisseRepository = $registry->getRepository(Caisse::class);
        $caisses = $caisseRepository->findAll();
        $convertedAmount = null;
        if (!$operation) {
            $this->addFlash('error', 'La vente que vous essayez de modifier n\'a pas été trouvée.');
            return $this->redirectToRoute('app_vente_index');
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
            $convertedAmount = $operation->getMontant() * $operation->getValeurVente();
            $montantDiff = $convertedAmount;
          

            $this->addFlash('success', 'La vente a été mise à jour avec succès.');
            return $this->render('vente/edit.html.twig', [
                'operation' => $operation,
                'devises' => $devises,
                'caisses' => $caisses,
                'convertedAmount' => $convertedAmount, // Ajouter la variable montantDiff à la vue
            ]);
        }
    
        return $this->render('vente/edit.html.twig', [
            'operation' => $operation,
            'devises' => $devises,
            'caisses' => $caisses,
            'convertedAmount' => $convertedAmount,
        ]);
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
    
    #[Route('/imprimervente', name: 'app_imprimer_vente1')]
    public function imprimer(): Response
    {
        return $this->render('achat/imprimer.html.twig', [
          
        ]);
    }
    #[Route('/imprimervnete', name: 'app_imprimer_vente')]
    public function imprimer2(): Response
    {
        return $this->render('achat/imprimer2.html.twig', [
          
        ]);
    }
}