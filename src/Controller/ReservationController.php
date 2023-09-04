<?php

namespace App\Controller;
use Swift_Mailer;
use App\Entity\Document;
use App\Entity\Reservation;
use App\Form\ReservationType;
use Symfony\Component\Mime\Email;
use App\Repository\DevisesRepository;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route ('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservationRepository->findAll(),
        ]);
    }
    #[Route('/new', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager, DevisesRepository $devisesRepository, DocumentRepository $documentRepository): Response
    {
        $devises = $devisesRepository->findAll();
    
        $code = $request->request->get('code');
        $valeurachat = $request->request->get('valeurachat');
        $valeurvente = $request->request->get('valeurvente');
        $montant = $request->request->get('montant');
    
        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $user->addReservation($reservation);
    
            foreach ($request->files->get('reservation')['documents'] as $uploadedFile) {
                if ($uploadedFile instanceof UploadedFile) {
                    $fileName = $uploadedFile->getClientOriginalName(); // Or generate a unique name
                    $uploadedFile->move($this->getParameter('upload_directory_fichiers'), $fileName);
    
                    $document = new Document();
                    $document->setLibelle($fileName);
                    $entityManager->persist($document);
    
                    $reservation->addDocument($document);
                }
            }
    
    
            $entityManager->persist($reservation);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_liste_reservations', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->renderForm('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
            'devises' => $devises,
            'montant' => $montant,
            'code' => $code,
            'valeurvente' => $valeurvente,
            'valeurachat' => $valeurachat,
            'error' => null,
            'success' => null
        ]);
    }

  /**
 * @Route("/reservation/{id}", name="app_reservation_show")
 * @ParamConverter("reservation", class="App\Entity\Reservation")
 */
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservationRepository->save($reservation, true);

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, ReservationRepository $reservationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $reservationRepository->remove($reservation, true);
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }

   
    
    #[Route('/{id}/confirm', name: 'app_reservation_confirm', methods: ['GET'])]
    public function confirm(Request $request, Reservation $reservation): Response
    {
        // Votre logique de confirmation de réservation ici
    
        // Envoyer un e-mail de confirmation à l'adresse du client
        $email = (new Email())
            ->from('admin@example.com')
            ->to($reservation->getUser()->getEmail())
            ->subject('Confirmation de réservation')
            ->html($this->renderView(
                'reservation/confirmation_email.html.twig',
                ['reservation' => $reservation]
            ));
    
        $message = $email->toString();
        $headers = $email->getHeaders();
    
        // Envoyer l'e-mail en utilisant la fonction mail de PHP
        mail($headers->get('To')->toString(), $headers->get('Subject')->toString(), $message, $headers->toString());
    
        // Reste de votre code
    }
    
   
    #[Route('/confirmed-reservations', name: 'app_confirmed_reservations')]
    public function confirmedReservations(EntityManagerInterface $entityManager): Response
    {
        // Récupérer les réservations confirmées depuis la base de données (exemple)
        $confirmedReservations = $entityManager->getRepository(Reservation::class)->findBy(['confirmation' => true]);
    
        return $this->render('reservation/confirmed_reservations.html.twig', [
            'reservations' => $confirmedReservations,
        ]);
    }
    

}
