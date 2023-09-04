<?php

namespace App\Controller;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Entity\Operation;
use App\Form\OperationType;
use App\Entity\Mouvement;
use App\Form\MouvementType;
use App\Repository\OperationRepository;
use App\Repository\CaisseRepository;
use App\Repository\DevisesRepository;
use App\Repository\TypeOperationRepository;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Client;
use App\Entity\Devises;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/operation')]
class OperationController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/', name: 'app_operation_index', methods: ['GET'])]
    public function index(OperationRepository $operationRepository): Response
    {
        return $this->render('operation/index.html.twig', [
            'operations' => $operationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_operation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, OperationRepository $operationRepository, ManagerRegistry $registry): Response
    {
        $devises = $registry->getRepository(Devises::class)->findAll();
        $operation = new Operation();
        $form = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operationRepository->save($operation, true);
            return $this->redirectToRoute('operation_add_mouvement', ['id' => $operation->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('operation/new.html.twig', [
            'operation' => $operation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_operation_show', methods: ['GET'])]
    public function show(Operation $operation): Response
    { //$pmpa = $operation->calculatePMPA();
      //  $pmpv = $operation->calculatePMPV();
    
        return $this->render('operation/show.html.twig', [
            'operation' => $operation,
         
       // 'pmpa' => $pmpa,
        //'pmpv' => $pmpv,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_operation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Operation $operation, OperationRepository $operationRepository): Response
    {
        $form = $this->createForm(OperationType::class, $operation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $operationRepository->save($operation, true);

            return $this->redirectToRoute('app_operation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('operation/edit.html.twig', [
            'operation' => $operation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_operation_delete', methods: ['POST'])]
    public function delete(Request $request, Operation $operation, OperationRepository $operationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$operation->getId(), $request->request->get('_token'))) {
            $operationRepository->remove($operation, true);
        }

        return $this->redirectToRoute('app_operation_index', [], Response::HTTP_SEE_OTHER);
    }
     /**
 * @Route("/{id}/mouvements", name="app_operation_mouvements", methods={"GET"})
 */
public function mouvements(Operation $operation): Response
{
    $mouvements = $operation->getMouvements();
    return $this->render('operation/mouvements.html.twig', [
        'operation' => $operation,
        'mouvements' => $mouvements,
    ]);
}
/**
 * @Route("/{id}/add-mouvement", name="operation_add_mouvement")
 */
public function addMouvement(Operation $operation, Request $request, EntityManagerInterface $entityManager): Response
{
    $mouvement = new Mouvement();
    $mouvement->setOperation($operation);

    $form = $this->createForm(MouvementType::class, $mouvement);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        if (!$operation->getMouvements()->contains($mouvement)) {
            $operation->addMouvement($mouvement);

            $sensMouvement = $operation->getSensMouvement();
            if ($sensMouvement) {
                $mouvement->setSensMouvement($sensMouvement);
            }

            $caisse = $mouvement->getCaisse();
            $caisse->setSolde($caisse->getSolde() + ($sensMouvement === 'E' ? $mouvement->getValeurDevise() : -$mouvement->getValeurDevise()));
            $entityManager->persist($caisse);
            $entityManager->persist($mouvement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_operation_index', ['id' => $operation->getId()]);
    }

    return $this->render('operation/add_mouvement.html.twig', [
        'form' => $form->createView(),
        'operation' => $operation,
    ]);
}



/**
     * @Route("/create", name="app_create_operation", methods={"POST"})
     */

    public function createOperation(Request $request): Response
    {
        // Créer une nouvelle instance de l'entité Operation
        $operation = new Operation();

        // Définir les propriétés de l'opération à partir des données de la requête
        $operation->setTypeoperation($request->request->get('typeoperation'));
        $operation->setClient($request->request->get('client'));

        // Persister l'opération en base de données
        $this->entityManager->persist($operation);
        $this->entityManager->flush();

        // Retourner une réponse JSON contenant l'ID de l'opération créée
        return $this->json(['id' => $operation->getId()]);
    }

    #[Route('/historique', name: 'historique', methods: ['GET'])]
    public function index1(OperationRepository $operationRepository): Response
    {
        $operations = $operationRepository->findAllSortedByDateDesc();

        return $this->render('operation/historique.html.twig', [
            'operations' => $operations,
        ]);
    }
    
}