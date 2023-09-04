<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\CaisseRepository;
use App\Repository\DevisesRepository;
use App\Repository\CoursdevisesRepository;

class ChangeDevisesService
{
    private $entityManager;
    private $caisseRepository;
    private $devisesRepository;
    private $coursdevisesRepository;

    public function __construct(EntityManagerInterface $entityManager, CaisseRepository $caisseRepository, DevisesRepository $devisesRepository, CoursDevisesRepository $coursDevisesRepository)
    {
        $this->entityManager = $entityManager;
        $this->caisseRepository = $caisseRepository;
        $this->devisesRepository = $devisesRepository;
        $this->coursdevisesRepository = $coursDevisesRepository;
    }


public function changeDevises(int $caisseId, int $deviseId, float $nouveauTaux): void
{
    $caisse = $this->caisseRepository->find($caisseId);
    $devise = $this->devisesRepository->find($deviseId);
    $coursDevises = $this->coursdevisesRepository->findOneBy(['devises' => $devise]);

    $coursDevises->setValeurAchat($nouveauTaux);
    $coursDevises->setValeurVente($nouveauTaux);

    $solde = $caisse->getSolde() * $nouveauTaux;
    $caisse->setSolde($solde);

    $this->entityManager->flush();
}
}