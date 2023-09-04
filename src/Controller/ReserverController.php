<?php

namespace App\Controller;

use App\Entity\Document; 
use App\Entity\Reservation;
use App\Repository\DevisesRepository;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;


    class ReserverController extends AbstractController
    {
        #[Route('/reserver', name: 'app_reserver', methods: ['GET', 'POST'])]
        public function create(Request $request, EntityManagerInterface $entityManager, DevisesRepository $devisesRepository): Response
        {
            $devises = $devisesRepository->findAll();
            $documents = $entityManager->getRepository(Document::class)->findAll();

            $code = $request->request->get('code');
            $valeurachat = $request->request->get('valeurachat');
            $valeurvente = $request->request->get('valeurvente');
            $montant = $request->request->get('montant');
    
            $devise = null; // Initialize $devise with a default value
    
            if ($request->isMethod('POST')) {
                $devise = $devisesRepository->findOneBy(['code' => $code]);
    
                if (!$devise) {
                    throw $this->createNotFoundException('La devise sélectionnée n\'existe pas.');
                }
    
                $reservation = new Reservation();
                $reservation->setDevises($devise);
                $reservation->setMontant($montant);
    
                // Assuming you have an input field in the form named "documentIds" that contains the IDs of selected documents
                $documentIds = $request->request->get('documentIds');
    
                if ($documentIds && is_array($documentIds)) {
                    $documents = $entityManager->getRepository(Document::class)->findBy(['id' => $documentIds]);
                    foreach ($documents as $document) {
                        $reservation->addDocument($document); // Add each selected document to the reservation
                    }
                }
    
                $user = $this->getUser();
                $user->addReservation($reservation); // Add the reservation to the current user
    
                $entityManager->persist($reservation);
                $entityManager->flush();
    
                // Redirection vers la liste des réservations de l'utilisateur
                return $this->redirectToRoute('app_liste_reservations');
            }
    
            return $this->render('reserver/index.html.twig', [
                'devises' => $devises,
                'devise' => $devise,
                'montant' => $montant,
                'code' => $code,
                'documents' => $documents,
                'valeurvente' => $valeurvente,
                'valeurachat' => $valeurachat,
                'error' => null,
                'success' => null,
            ]);
        }
    #[Route('/liste-reservations', name: 'app_liste_reservations')]
    public function listeReservations(SessionInterface $session): Response
    {
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('L\'utilisateur courant n\'est pas défini.');
        }

        $reservations = $user->getReservations();
        $confirmedReservations = [];

        foreach ($reservations as $reservation) {
            if ($reservation->getConfirmation()) {
                $confirmedReservations[] = $reservation;
            }
        }

        return $this->render('reserver/liste_reservations.html.twig', [
            'reservations' => $reservations,
            'confirmed_reservations' => $confirmedReservations,
            'error' => null,
            'success' => null,
        ]);
    }
    #[Route('/confirmation-reservation/{id}', name: 'app_confirmation_reservation')]
    public function confirmReservation(Request $request, EntityManagerInterface $entityManager, Reservation $reservation): Response
    {
        if (!$reservation) {
            throw $this->createNotFoundException('La réservation n\'existe pas.');
        }
    
        if ($request->isMethod('POST')) {
            $confirmation = $request->request->get('confirmation');
            // Enregistrez la confirmation dans l'entité Reservation
            $reservation->setConfirmation($confirmation);
    
            $entityManager->persist($reservation);
            $entityManager->flush();
    
            // Redirection vers une page de succès ou une autre page appropriée
            return $this->redirectToRoute('app_reservation_index');
        }
    
        return $this->render('reserver/confirmation.html.twig', [
            'reservation' => $reservation,
        ]);
    }
    #[Route('/annuler-reservation/{id}', name: 'app_annuler_reservation')]
public function annulerReservation(Request $request, EntityManagerInterface $entityManager, Reservation $reservation): Response
{
    if (!$reservation) {
        throw $this->createNotFoundException('La réservation n\'existe pas.');
    }

    // Supprimer la réservation
    $entityManager->remove($reservation);
    $entityManager->flush();

    // Ajoutez un message flash pour afficher une notification d'annulation
    $this->addFlash('success', 'La réservation a été annulée.');

    // Redirection vers la liste des réservations de l'utilisateur
    return $this->redirectToRoute('app_liste_reservations');
}
#[Route('/reservations-confirmees', name: 'app_reservations_confirmees')]
public function reservationsConfirmees(): Response
{
    $user = $this->getUser(); // Get the current user

    $confirmed_reservations = []; // Initialize $confirmed_reservations with an empty array

    if ($user) {
        $reservations = $user->getReservations(); // Get all reservations of the user

        // Filter confirmed reservations
        foreach ($reservations as $reservation) {
            if ($reservation->getConfirmation()) {
                $confirmed_reservations[] = $reservation;
            }
        }
    } else {
        throw $this->createNotFoundException('The current user is not defined.');
    }

    return $this->render('reserver/reservations_confirmees.html.twig', [
        'confirmed_reservations' => $confirmed_reservations,
        'error' => null,
        'success' => null,
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


}