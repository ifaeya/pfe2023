<?php

namespace App\Controller;

use App\Entity\Devises;
use App\Form\DevisesType;
use App\Repository\DevisesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/devises')]
class DevisesController extends AbstractController
{/**
 * @var \Symfony\Component\HttpFoundation\File\UploadedFile
 */
    #[Route('/', name: 'app_devises_index', methods: ['GET'])]
    public function index(DevisesRepository $devisesRepository): Response
    {
        return $this->render('devises/index.html.twig', [
            'devises' => $devisesRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_devises_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DevisesRepository $devisesRepository, SluggerInterface $slugger): Response
    {
        $devise = new Devises();
        $form = $this->createForm(DevisesType::class, $devise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var uploadedFile $file
             */

            $file= $form->get('image')->getData();
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $newFileName = $slugger->slug($fileName);
            $imageName=uniqid() . $newFileName.$file->guessExtension();

            try {
                $file->move($this->getParameter('upload_directory'), $imageName);

            } catch(FileException $exception) {
                echo $exception->getMessage();
                die;

            }


           $devise->setImage($imageName);
            $devisesRepository->save($devise, true);
            return $this->redirectToRoute('app_devises_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('devises/new.html.twig', [
            'devise' => $devise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_devises_show', methods: ['GET'])]
    public function show(Devises $devise): Response
    {
        return $this->render('devises/show.html.twig', [
            'devise' => $devise,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_devises_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Devises $devise, DevisesRepository $devisesRepository, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(DevisesType::class, $devise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
                      * @var uploadedFile $file
                      */

            $file= $form->get('image')->getData();
            $fileName = pathinfo($file, PATHINFO_FILENAME);
            $newFileName = $slugger->slug($fileName);
            $imageName=uniqid() . $newFileName.$file->guessExtension();

            try {
                $file->move($this->getParameter('upload_directory'), $imageName);

            } catch(FileException $exception) {
                echo $exception->getMessage();
                die;

            }


            $devise->setImage($imageName);

            $devisesRepository->save($devise, true);

            return $this->redirectToRoute('app_devises_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('devises/edit.html.twig', [
            'devise' => $devise,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_devises_delete', methods: ['POST'])]
    public function delete(Request $request, Devises $devise, DevisesRepository $devisesRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$devise->getId(), $request->request->get('_token'))) {
            $devisesRepository->remove($devise, true);
        }

        return $this->redirectToRoute('app_devises_index', [], Response::HTTP_SEE_OTHER);
    }
    public function convertirAction(Request $request)
    {
        // Récupérer le montant et la devise cible du formulaire
        $montant = $request->request->get('montant');
        $deviseCible = $request->request->get('deviseCible');

        // Créer une instance de l'entité Devises
        $devises = new Devises();

        // Appeler la méthode convertir() pour effectuer la conversion
        $resultat = $devises->convertir($deviseCible, $montant);

        // Passer le résultat à la vue Twig
        return $this->render('conversion.html.twig', [
            'resultat' => $resultat
        ]);
    }

    /**
 * @Route("/devises/update", name="app_devise_update")
 */
public function update(Request $request, EntityManagerInterface $entityManager)
{
    $id = $request->request->get('id');
    $field = $request->request->get('field');
    $value = $request->request->get('value');

    $devise = $entityManager->getRepository(Devises::class)->find($id);

    if (!$devise) {
        throw $this->createNotFoundException('No course found for id '.$id);
    }

    switch ($field) {
        case 'libelle':
            $devise->setLibelle($value);
            break;
        case 'unite':
            $devise->setUnite($value);
            break;
        case 'abreviation':
            $devise->setAbreviation($value);
            break;
            case 'code':
                $devise->setCode($value);
                break;
        default:
            throw new InvalidArgumentException('Invalid field name: '.$field);
    }

    $entityManager->flush();

    return new JsonResponse(['success' => true]);
}







}
