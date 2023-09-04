<?php

namespace App\Controller;

use App\Entity\Coursdevises;
use App\Form\CoursdevisesType;
use App\Repository\CoursdevisesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use InvalidArgumentException;
use App\Entity\ArchiveCoursdevises;
use App\Repository\ArchiveCoursdevisesRepository;

#[Route('/coursdevises')]
class CoursdevisesController extends AbstractController
{
    private $entityManager;
    private CoursdevisesRepository $coursdevisesRepository;

    public function __construct(EntityManagerInterface $entityManager, CoursdevisesRepository $coursdevisesRepository)
    {
        $this->coursdevisesRepository = $coursdevisesRepository;

        $this->entityManager = $entityManager;
    }
    #[Route('/', name: 'app_coursdevises_index', methods: ['GET'])]
    public function index(CoursdevisesRepository $coursdevisesRepository): Response
    {
        return $this->render('coursdevises/index.html.twig', [
            'coursdevises' => $coursdevisesRepository->findByUpdatedAt(),

        ]);
    }

    #[Route('/new', name: 'app_coursdevises_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CoursdevisesRepository $coursdevisesRepository , EntityManagerInterface $entityManager): Response
    {
        $coursdevise = new Coursdevises($entityManager);
        $form = $this->createForm(CoursdevisesType::class, $coursdevise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->coursdevisesRepository->save($coursdevise, true);

            return $this->redirectToRoute('app_coursdevises_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('coursdevises/new.html.twig', [
            'coursdevise' => $coursdevise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_coursdevises_show', methods: ['GET'])]
    public function show(Coursdevises $coursdevise): Response
    {
        return $this->render('coursdevises/show.html.twig', [
            'coursdevise' => $coursdevise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_coursdevises_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Coursdevises $coursdevise, CoursdevisesRepository $coursdevisesRepository): Response
    {
        $form = $this->createForm(CoursdevisesType::class, $coursdevise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coursdevisesRepository->save($coursdevise, true);

            return $this->redirectToRoute('app_coursdevises_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('coursdevises/edit.html.twig', [
            'coursdevise' => $coursdevise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_coursdevises_delete', methods: ['POST'])]
    public function delete(Request $request, Coursdevises $coursdevise, CoursdevisesRepository $coursdevisesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$coursdevise->getId(), $request->request->get('_token'))) {
            $coursdevisesRepository->remove($coursdevise, true);
        }

        return $this->redirectToRoute('app_coursdevises_index', [], Response::HTTP_SEE_OTHER);
    }/**
 * @Route("/coursdevises/update", name="app_coursdevises_update", methods={"POST"})
 */
    public function update(
        Request $request,
        EntityManagerInterface $entityManager,
    ) {
        $id = $request->request->get('id');
        $field = $request->request->get('field');
        $value = $request->request->get('value');

        $coursdevise = $entityManager->getRepository(Coursdevises::class)->find($id);

        if (!$coursdevise) {
            throw $this->createNotFoundException('No course found for id ' . $id);
        }

        // Create an archive of the previous values
        $archive = new ArchiveCoursdevises();
        $archive->setValeurachat($coursdevise->getValeurachat());
        $archive->setValeurvente($coursdevise->getValeurvente());
        $archive->setDateArchivage(new \DateTime());
        $archive->setCours($coursdevise);

        // Use a switch with constants for the field names
        switch ($field) {
            case 'valeurachat':
                $coursdevise->setValeurachat($value);
                break;
            case 'valeurvente':
                $coursdevise->setValeurvente($value);
                break;
            default:
                throw new InvalidArgumentException('Invalid field name: ' . $field);
        }

        $entityManager->persist($archive);
        $entityManager->persist($coursdevise);  // Persister l'entité coursdevise mise à jour
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
