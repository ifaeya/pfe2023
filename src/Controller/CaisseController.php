<?php

namespace App\Controller;

use App\Entity\Caisse;
use App\Form\CaisseType;
use App\Repository\CaisseRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

#[Route('/caisse')]
class CaisseController extends AbstractController
{
    private $entityManager;
    public function __construct(ManagerRegistry $registry)
    {
        $this->entityManager = $registry->getManager();
    }
    #[Route('/', name: 'app_caisse_index', methods: ['GET'])]
    public function index(CaisseRepository $caisseRepository): Response
    {

        return $this->render('caisse/index.html.twig', [
            'caisses' => $caisseRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_caisse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CaisseRepository $caisseRepository): Response
    {
        $caisse = new Caisse($caisseRepository);
        // Vérifie si la caisse est fermée
        if ($caisse->Fermee()) {
            $this->addFlash('warning', 'Impossible de créer une nouvelle caisse, la caisse est fermée.');
            return $this->redirectToRoute('app_caisse_index');
        }

        $form = $this->createForm(CaisseType::class, $caisse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $caisseRepository->save($caisse, true);

            return $this->redirectToRoute('app_caisse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('caisse/new.html.twig', [
            'caisse' => $caisse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_caisse_show', methods: ['GET'])]
    public function show(Caisse $caisse): Response
    {    // Vérifie si la caisse est fermée
        if ($caisse->Fermee()) {
            $this->addFlash('warning', 'La caisse est fermée.');
            return $this->redirectToRoute('app_caisse_index');
        }

        return $this->render('caisse/show.html.twig', [
            'caisse' => $caisse,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_caisse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Caisse $caisse, CaisseRepository $caisseRepository): Response
    {    // Vérifie si la caisse est fermée
        if ($caisse->Fermee()) {
            $this->addFlash('warning', 'La caisse est fermée.');
            return $this->redirectToRoute('app_caisse_index');
        }

        $form = $this->createForm(CaisseType::class, $caisse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $caisseRepository->save($caisse, true);

            return $this->redirectToRoute('app_caisse_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('caisse/edit.html.twig', [
            'caisse' => $caisse,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_caisse_delete', methods: ['POST'])]
    public function delete(Request $request, Caisse $caisse, CaisseRepository $caisseRepository): Response
    { // Vérifie si la caisse est fermée
        if ($caisse->Fermee()) {
            $this->addFlash('warning', 'La caisse est fermée.');
            return $this->redirectToRoute('app_caisse_index');
        }
        if ($this->isCsrfTokenValid('delete'.$caisse->getId(), $request->request->get('_token'))) {
            $caisseRepository->remove($caisse, true);
        }

        return $this->redirectToRoute('app_caisse_index', [], Response::HTTP_SEE_OTHER);
    }
     /**
     * @Route("/{id}/stocks", name="caisse_stocks")
     */
    public function showCaisseStocks(int $id): Response
    {

        $caisse = $this->entityManager->getRepository(Caisse::class)->find($id);

        // Vérifie si la caisse est fermée
        if ($caisse->Fermee()) {
            $this->addFlash('warning', 'La caisse est fermée.');
            return $this->redirectToRoute('app_caisse_index');
        }
        if (!$caisse) {
            throw $this->createNotFoundException('La caisse demandée n\'existe pas.');
        }

        $stocks = $caisse->getStocks();

        return $this->render('caisse/stocks.html.twig', [
            'caisse' => $caisse,
            'stocks' => $stocks,
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
