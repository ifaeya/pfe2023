<?php

namespace App\Entity;

use App\Repository\OperationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OperationRepository::class)]

class Operation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(cascade: ["persist"], inversedBy: "operation")]
    private ?Client $client = null;

    #[ORM\ManyToOne(cascade: ["persist"],inversedBy: 'operation')]
    private ?Typeoperation $typeoperation = null;

    #[ORM\Column(nullable: true,length: 255)]
    private ?string $status = null;

    #[ORM\OneToMany(mappedBy: 'operation', targetEntity: Mouvement::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $mouvements;

    #[ORM\ManyToMany(targetEntity: Document::class, mappedBy: 'operation', cascade: ["persist"])]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $documents;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->mouvements = new ArrayCollection();
        $this->documents = new ArrayCollection();

    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }



    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getTypeoperation(): ?Typeoperation
    {
        return $this->typeoperation;
    }

    public function setTypeoperation(?Typeoperation $typeoperation): self
    {
        $this->typeoperation = $typeoperation;

        return $this;
    }

    /**
     * @return Collection|Mouvement[]
     */
    public function getMouvements(): Collection
    {
        return $this->mouvements;
    }
    public function addMouvement(Mouvement $mouvement): self
    {
        if (!$this->mouvements->contains($mouvement)) {
            $this->mouvements->add($mouvement);
            $mouvement->setOperation($this);
            $sensMouvement = $this->getSensMouvement();
            if ($sensMouvement) {
                $mouvement->setSensMouvement($sensMouvement);
            }

            // Ajoute l'opération à la collection de mouvements de ce mouvement
            $mouvement->getCaisse()->addMouvement($mouvement);

            // Update the balance of the Caisse entity
            $caisse = $mouvement->getCaisse();
            $caisse->setSolde($caisse->getSolde() + ($sensMouvement === 'E' ? $mouvement->getValeurDevise() : -$mouvement->getValeurDevise()));

            // Update the balance of the Client entity
            $client = $this->getClient();
            if ($client) {
                $client->setSolde($client->getSolde() + ($sensMouvement === 'E' ? -$mouvement->getValeurDevise() : $mouvement->getValeurDevise()));
            }
        }

        return $this;
    }



        public function removeMouvement(Mouvement $mouvement): self
        {
            if ($this->mouvements->contains($mouvement)) {
                $this->mouvements->removeElement($mouvement);
                // set the owning side to null (unless already changed)
                if ($mouvement->getOperation() === $this) {
                    $mouvement->setOperation(null);
                }
            }

            return $this;
        }

    /**
         * @return Collection<int, Document>
         */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
            $document->addOperation($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->removeElement($document)) {
            $document->removeOperation($this);
        }

        return $this;
    }
    public function __toString()
    {
        return strval($this->typeoperation);
    }



    /**
         * Set the value of createdAt
         *
         * @return  self
         */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getValeurDevise(): ?float
    {
        $valeur = 0;

        foreach ($this->getMouvements() as $mouvement) {
            $valeur += $mouvement->getValeurDevise();
            if ($mouvement->getSensMouvement() === 'E') {
                $valeur += $mouvement->getValeurDevise();
            } elseif ($mouvement->getSensMouvement() === 'S') {
                $valeur -= $mouvement->getValeurDevise();
            }
        }

        return $valeur;
    }

public function getCaisse(): ?Caisse
{
    $caisse = null;

    foreach ($this->getMouvements() as $mouvement) {
        if ($mouvement->getCaisse()) {
            $caisse = $mouvement->getCaisse();
            break;
        }
    }

    return $caisse;
}

public function getDevise(): ?Devises
{
    $devise = null;

    foreach ($this->getMouvements() as $mouvement) {
        if ($mouvement->getDevise()) {
            $devise = $mouvement->getDevise();
            break;
        }
    }

    return $devise;
}


public function getSensMouvement(): ?string
{
    $sensMouvement = null;
    $typeOperation = $this->getTypeoperation();

    if ($typeOperation instanceof Typeoperation) {
        switch ($typeOperation->getLibelle()) {
            case 'achat':
                $sensMouvement = 'E';
                break;
            case 'vente':
                $sensMouvement = 'S';
                break;
                // ajoutez d'autres cas pour chaque type d'opération
        }
    }

    return $sensMouvement;
}

 public function setSensMouvement(): self
 {
     if ($this->typeoperation instanceof Typeoperation) {
         if ($this->typeoperation->getLibelle() === 'achat') {
             foreach ($this->mouvements as $mouvement) {
                 $mouvement->setSensMouvement('E');
             }
         } elseif ($this->typeoperation->getLibelle() === 'vente') {
             foreach ($this->mouvements as $mouvement) {
                 $mouvement->setSensMouvement('S');
             }
         }
     }
 
     return $this;
 }
 

 // Calculate PMPA (Dinars décaissés / Devises achetés) for a specific operation
public function getPMPA(): ?float
{
    $dinarsDecaisses = 0;
    $devisesAchetes = 0;

    foreach ($this->getMouvements() as $mouvement) {
        if ($mouvement->getSensMouvement() === 'E') {
            // Décaissés (Dinars décaissés)
            $dinarsDecaisses += $mouvement->getValeurDevise();
        } elseif ($mouvement->getSensMouvement() === 'S') {
            // Achetés (Devises achetés)
            $devisesAchetes += $mouvement->getValeurDevise();
        }
    }

    if ($devisesAchetes != 0) {
        return $dinarsDecaisses / $devisesAchetes;
    } else {
        return null; // Handle division by zero scenario
    }
}

// Calculate PMPV (Dinars encaissés / Devises vendues) for a specific operation
public function getPMPV(): ?float
{
    $dinarsEncaisses = 0;
    $devisesVendues = 0;

    foreach ($this->getMouvements() as $mouvement) {
        if ($mouvement->getSensMouvement() === 'S') {
            // Encaissés (Dinars encaissés)
            $dinarsEncaisses += $mouvement->getValeurDevise();
        } elseif ($mouvement->getSensMouvement() === 'E') {
            // Vendues (Devises vendues)
            $devisesVendues += $mouvement->getValeurDevise();
        }
    }

    if ($devisesVendues != 0) {
        return $dinarsEncaisses / $devisesVendues;
    } else {
        return null; // Handle division by zero scenario
    }
}

public function getMargeRealisee(): ?float
{
    $pmpa = $this->getPMPA();
    $pmpv = $this->getPMPV();

    if ($pmpa !== null && $pmpv !== null) {
        return $pmpv - $pmpa;
    } else {
        return null; // Gérer le cas de division par zéro
    }
}

// Calcul du résultat réalisé (Marge * somme (devises vendues)) pour une opération spécifique
public function getResultatRealise(): ?float
{
    $margeRealisee = $this->getMargeRealisee();
    $devisesVendues = $this->getDevisesVendues();

    if ($margeRealisee !== null && $devisesVendues !== 0) {
        return $margeRealisee * $devisesVendues;
    } else {
        return null; // Gérer le cas de division par zéro
    }
}

// Calcul de la marge latente (P.V Actuel - PMPA de la Position) pour une opération spécifique
public function getMargeLatente(): ?float
{
    $pvActuel = $this->getPositionVenteActuelle();
    $pmpaPosition = $this->getPMPA();

    if ($pvActuel !== null && $pmpaPosition !== null) {
        return $pvActuel - $pmpaPosition;
    } else {
        return null; // Gérer le cas de valeurs manquantes
    }
}


// Calcul de la somme des devises vendues pour une opération spécifique
public function getDevisesVendues(): float
{
    $devisesVendues = 0;

    foreach ($this->getMouvements() as $mouvement) {
        if ($mouvement->getSensMouvement() === 'S') {
            $devisesVendues += $mouvement->getValeurDevise();
        }
    }

    return $devisesVendues;
}
public function getPositionVenteActuelle(): ?float
{
    // Initialiser les sommes des achats et des ventes à zéro
    $sommeAchats = 0;
    $sommeVentes = 0;

    // Parcourir les mouvements associés à l'opération
    foreach ($this->getMouvements() as $mouvement) {
        // Vérifier le sens du mouvement
        if ($mouvement->getSensMouvement() === 'E') {
            // Si c'est un mouvement d'achat (sens 'E'), ajouter la valeur du mouvement à la somme des achats
            $sommeAchats += $mouvement->getValeurDevise();
        } elseif ($mouvement->getSensMouvement() === 'S') {
            // Si c'est un mouvement de vente (sens 'S'), ajouter la valeur du mouvement à la somme des ventes
            $sommeVentes += $mouvement->getValeurDevise();
        }
    }

    // Calculer la "Position de Vente Actuelle" en soustrayant la somme des achats de la somme des ventes
    $positionVenteActuelle = $sommeVentes - $sommeAchats;

    return $positionVenteActuelle;
}


// Résultat Latent : Marge Latente * Position
public function getRsultatLatent(): ?float
{
    $margeLatente = $this->getMargeLatente();
    $position = $this->getPositionVenteActuelle();

    if ($margeLatente !== null && $position !== null) {
        return $margeLatente * $position;
    } else {
        return null; // Gérer le cas de valeurs manquantes
    }
}

// Point Mort : PMPA — (Résultat réalisé / Position)
public function getPointMort(): ?float
{
    $pmpaPosition = $this->getPMPA();
    $resultatRealise = $this->getResultatRealise();
    $position = $this->getPositionVenteActuelle();

    if ($pmpaPosition !== null && $resultatRealise !== null && $position !== null) {
        return $pmpaPosition - ($resultatRealise / $position);
    } else {
        return null; // Gérer le cas de valeurs manquantes
    }
}

// Résultat par objectif à la baisse : PMPA - (Marge à la baisse / Position)
public function getResultatObjectifBaisse($margeBaisse): ?float
{
    $pmpaPosition = $this->getPMPA();
    $position = $this->getPositionVenteActuelle();

    if ($pmpaPosition !== null && $margeBaisse !== null && $position !== null) {
        return $pmpaPosition - ($margeBaisse / $position);
    } else {
        return null; // Gérer le cas de valeurs manquantes
    }
}

// Résultat par objectif à la hausse : PMPA + (Marge à la hausse / Position)
public function getResultatObjectifHausse($margeHausse): ?float
{
    $pmpaPosition = $this->getPMPA();
    $position = $this->getPositionVenteActuelle();

    if ($pmpaPosition !== null && $margeHausse !== null && $position !== null) {
        return $pmpaPosition + ($margeHausse / $position);
    } else {
        return null; // Gérer le cas de valeurs manquantes
    }
}


    /**
     * Get the value of status
     */ 
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set the value of status
     *
     * @return  self
     */ 
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
