<?php

namespace App\Controller;

use App\Entity\Typeoperation;
use App\Form\TypeoperationType;
use App\Repository\TypeoperationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/typeoperation')]
class TypeoperationController extends AbstractController
{
    #[Route('/', name: 'app_typeoperation_index', methods: ['GET'])]
    public function index(TypeoperationRepository $typeoperationRepository): Response
    {
        return $this->render('typeoperation/index.html.twig', [
            'typeoperations' => $typeoperationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_typeoperation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, TypeoperationRepository $typeoperationRepository): Response
    {
        $typeoperation = new Typeoperation();
        $form = $this->createForm(TypeoperationType::class, $typeoperation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeoperationRepository->save($typeoperation, true);

            return $this->redirectToRoute('app_typeoperation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('typeoperation/new.html.twig', [
            'typeoperation' => $typeoperation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_typeoperation_show', methods: ['GET'])]
    public function show(Typeoperation $typeoperation): Response
    {
        return $this->render('typeoperation/show.html.twig', [
            'typeoperation' => $typeoperation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_typeoperation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Typeoperation $typeoperation, TypeoperationRepository $typeoperationRepository): Response
    {
        $form = $this->createForm(TypeoperationType::class, $typeoperation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $typeoperationRepository->save($typeoperation, true);

            return $this->redirectToRoute('app_typeoperation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('typeoperation/edit.html.twig', [
            'typeoperation' => $typeoperation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_typeoperation_delete', methods: ['POST'])]
    public function delete(Request $request, Typeoperation $typeoperation, TypeoperationRepository $typeoperationRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$typeoperation->getId(), $request->request->get('_token'))) {
            $typeoperationRepository->remove($typeoperation, true);
        }

        return $this->redirectToRoute('app_typeoperation_index', [], Response::HTTP_SEE_OTHER);
    }
}
