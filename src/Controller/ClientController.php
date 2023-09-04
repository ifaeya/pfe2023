<?php

namespace App\Controller;

use App\Form\ClientType;
use App\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Client;
use App\Entity\Operation;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use App\Service\AllocationTouristiqueService;

#[Route('/client')]
class ClientController extends AbstractController
{
    
    #[Route('/', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ClientRepository $clientRepository): Response
    {
        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $clientRepository->save($client, true);


            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Request $request, ManagerRegistry $registry, $id): Response
    {
        $client = $registry->getRepository(Client::class)->find($id);

        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, ClientRepository $clientRepository): Response
    {
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clientRepository->save($client, true);

            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, ClientRepository $clientRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $clientRepository->remove($client, true);
        }

        return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
    }
    public function form(FormFactoryInterface $formFactory)
    {
        $client = new Client();
        $form = $formFactory->createBuilder()
            ->add('nom', TextType::class)
            ->add('prenom', TextType::class)
            ->add('numcin', TextType::class)
            ->add('datecin', DateType::class)
            ->add('numpasseport', TextType::class)
            ->add('datepasseport', DateType::class)
            ->add('datevalidpasport', DateType::class)
            ->add('save', SubmitType::class, ['label' => 'Enregistrer'])
            ->getForm();

        $view = $form->createView();
        $response = new Response(json_encode($view));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    /**
 * @Route("/{id}/operations", name="app_client_operations")
 */
public function getClientOperations(ClientRepository $clientRepository, int $id): Response
{
    $client = $clientRepository->find($id);

    if (!$client) {
        throw $this->createNotFoundException('Client non trouvé');
    }

    $operations = $client->getOperation();

    return $this->render('client/operations.html.twig', [
        'client' => $client,
        'operations' => $operations,
    ]);
}


}
