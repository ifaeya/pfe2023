<?php

// src/Service/PrixMoyenService.php

namespace App\Service;

use App\Repository\OperationRepository;
use App\Entity\Devises;

class PrixMoyenService
{
    private $operationRepository;

    public function __construct(OperationRepository $operationRepository)
    {
        $this->operationRepository = $operationRepository;
    }

    public function calculerPMPA(): float
    {
        // Logique pour calculer le PMPA à partir des opérations d'achat
        $operationsAchat = $this->operationRepository->findOperationsAchat(); // Remplacez par la méthode appropriée pour récupérer les opérations d'achat
        $totalDinarsDecaisses = 0;
        $totalDevisesAchetes = 0;

        foreach ($operationsAchat as $operation) {
            // Sum up the total amount in local currency (TND)
            $totalDinarsDecaisses += $operation->getMontantAchat();

            // Sum up the total amount in the purchased currency
            $totalDevisesAchetes += $operation->getMontantAchat() * $operation->getValeurAchat();
        }

        if ($totalDevisesAchetes === 0) {
            // Handle the case where no currencies were purchased
            return 0;
        }

        // Calculate the PMPA
        $pmpa = $totalDinarsDecaisses / $totalDevisesAchetes;

        return $pmpa;
    }

    public function calculerPMPV(): float
    {
        // Logique pour calculer le PMPV à partir des opérations passées
        $operationsVente = $this->operationRepository->findOperationsVente(); // Remplacez par la méthode appropriée pour récupérer les opérations de vente
        $totalDinarsEncaisses = 0;
        $totalDevisesVendues = 0;

        foreach ($operationsVente as $operation) {
            // Sum up the total amount in local currency (TND)
            $totalDinarsEncaisses += $operation->getMontant();

            // Sum up the total amount in the sold currency
            $totalDevisesVendues += $operation->getMontant() / $operation->getValeurVente();
        }

        if ($totalDevisesVendues === 0) {
            // Handle the case where no currencies were sold
            return 0;
        }

        // Calculate the PMPV
        $pmpv = $totalDinarsEncaisses / $totalDevisesVendues;

        return $pmpv;
    }
}

