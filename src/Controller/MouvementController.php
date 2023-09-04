<?php

namespace App\Controller;

use App\Entity\Mouvement;
use App\Form\MouvementType;
use App\Repository\MouvementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;


#[Route('/mouvement')]
class MouvementController extends AbstractController
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }
    #[Route('/', name: 'app_mouvement_index', methods: ['GET'])]
    public function index(MouvementRepository $mouvementRepository): Response
    {
        return $this->render('mouvement/index.html.twig', [
            'mouvements' => $mouvementRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_mouvement_new", methods={"GET","POST"})
     */
    public function new(Request $request, MouvementRepository $mouvementRepository, ManagerRegistry $managerRegistry): Response
    {
        $mouvement = new Mouvement();
        $form = $this->createForm(MouvementType::class, $mouvement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $managerRegistry->getManager();

            // Récupérer la caisse associée au mouvement
            $caisse = $mouvement->getCaisse();

            // Mettre à jour le solde de la caisse en fonction du nouveau mouvement ajouté
            $caisse->addMouvement($mouvement);

            // Enregistrer la caisse et le nouveau mouvement dans la base de données
            $entityManager->persist($caisse);
            $entityManager->persist($mouvement);
            $entityManager->flush();

            $this->addFlash('success', 'Le mouvement a été ajouté avec succès.');

            return $this->redirectToRoute('app_caisse_show', ['id' => $caisse->getId()]);
        }

        return $this->render('mouvement/new.html.twig', [
            'mouvement' => $mouvement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_mouvement_show', methods: ['GET'])]
    public function show(Mouvement $mouvement): Response
    {
        return $this->render('mouvement/show.html.twig', [
            'mouvement' => $mouvement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_mouvement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Mouvement $mouvement, MouvementRepository $mouvementRepository): Response
    {
        $form = $this->createForm(MouvementType::class, $mouvement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mouvementRepository->save($mouvement, true);

            return $this->redirectToRoute('app_mouvement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('mouvement/edit.html.twig', [
            'mouvement' => $mouvement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_mouvement_delete', methods: ['POST'])]
    public function delete(Request $request, Mouvement $mouvement, MouvementRepository $mouvementRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$mouvement->getId(), $request->request->get('_token'))) {
            $mouvementRepository->remove($mouvement, true);
        }

        return $this->redirectToRoute('app_mouvement_index', [], Response::HTTP_SEE_OTHER);
    }
}
