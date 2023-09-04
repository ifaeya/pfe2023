<?php

namespace App\Controller;

use App\Entity\ArchiveCoursdevises;
use App\Form\ArchiveCoursdevisesType;
use App\Repository\ArchiveCoursdevisesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/archive/coursdevises')]
class ArchiveCoursdevisesController extends AbstractController
{
    #[Route('/', name: 'app_archive_coursdevises_index', methods: ['GET'])]
    public function index(ArchiveCoursdevisesRepository $archiveCoursdevisesRepository): Response
    {
        $archivesByDate = [];

        $archiveCoursdevises = $archiveCoursdevisesRepository->findAll();
    
        foreach ($archiveCoursdevises as $archive) {
            $date = $archive->getDateArchivage()->format('Y-m-d');
    
            if (!isset($archivesByDate[$date])) {
                $archivesByDate[$date] = [];
            }
    
            $archivesByDate[$date][] = $archive;
        }
    
        return $this->render('archive_coursdevises/index.html.twig', [
            'archive_coursdevises' => $archiveCoursdevises,
            'archivesByDate' => $archivesByDate,
        ]);
    }



    #[Route('/{id}', name: 'app_archive_coursdevises_show', methods: ['GET'])]
    public function show(ArchiveCoursdevises $archiveCoursdevise): Response
    {
        return $this->render('archive_coursdevises/show.html.twig', [
            'archive_coursdevise' => $archiveCoursdevise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_archive_coursdevises_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ArchiveCoursdevises $archiveCoursdevise, ArchiveCoursdevisesRepository $archiveCoursdevisesRepository): Response
    {
        $form = $this->createForm(ArchiveCoursdevisesType::class, $archiveCoursdevise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $archiveCoursdevisesRepository->save($archiveCoursdevise, true);

            return $this->redirectToRoute('app_archive_coursdevises_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('archive_coursdevises/edit.html.twig', [
            'archive_coursdevise' => $archiveCoursdevise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_archive_coursdevises_delete', methods: ['POST'])]
    public function delete(Request $request, ArchiveCoursdevises $archiveCoursdevise, ArchiveCoursdevisesRepository $archiveCoursdevisesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$archiveCoursdevise->getId(), $request->request->get('_token'))) {
            $archiveCoursdevisesRepository->remove($archiveCoursdevise, true);
        }

        return $this->redirectToRoute('app_archive_coursdevises_index', [], Response::HTTP_SEE_OTHER);
    }


}
