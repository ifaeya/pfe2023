<?php

namespace App\Entity;

use App\Repository\CaisseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CaisseRepository::class)]

class Caisse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $libelle = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?float $solde = null;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: Mouvement::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $mouvements;

    #[ORM\ManyToOne(inversedBy: 'caisse')]
    private ?Devises $devises = null;

    #[ORM\OneToMany(mappedBy: 'caisse', targetEntity: Stock::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private Collection $stocks;
    #[ORM\Column(type: 'boolean')]
    private bool $fermee = false;



    public function __construct(CaisseRepository $caisseRepository)
    {
        $this->mouvements = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->caisseRepository = $caisseRepository;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

        return $this;
    }
    public function fermee(): bool
    {
        return $this->fermee;
    }

    public function getSolde(): ?float
    {
        return $this->solde;
    }

    public function setSolde(float $solde): self
    {
        $this->solde = $solde;

        return $this;
    }

    /**
     * @return Collection<int, Mouvement>
     */
    public function getMouvements(): Collection
    {
        return $this->mouvements;
    }

    public function addMouvement(Mouvement $mouvement): self
    {
        $this->mouvements[] = $mouvement;
        $mouvement->setCaisse($this);

        // Mettre à jour le solde de la caisse
        if ($mouvement->getSensMouvement() === 'E') {
            $this->setSolde($this->getSolde() + $mouvement->getValeurDevise());
        } elseif ($mouvement->getSensMouvement() === 'S') {
            $this->setSolde($this->getSolde() - $mouvement->getValeurDevise());
        }
        // Vérifier si le solde est supérieur ou égal à zéro
        if ($this->getSolde() <= 0) {
            throw new \Exception('Le solde de la caisse ne peut pas être négatif.');
        }
        return $this;
    }




    public function removeMouvement(Mouvement $mouvement): self
    {
        if ($this->mouvements->removeElement($mouvement)) {
            // set the owning side to null (unless already changed)
            if ($mouvement->getCaisse() === $this) {
                $mouvement->setCaisse(null);
            }
        }

        return $this;
    }

    public function getDevises(): ?Devises
    {
        return $this->devises;
    }

    public function setDevises(?Devises $devises): self
    {
        $this->devises = $devises;

        return $this;
    }
    public function __toString()
    {
        return strval($this->libelle);
    }
public function estVide()
{
    return $this->solde === 0;
}

/**
 * @return Collection<int, Stock>
 */
public function getStocks(): Collection
{
    return $this->stocks;
}

public function addStock(Stock $stock): self
{
    $this->stocks[] = $stock;
    $stock->setCaisse($this);

    // Mettre à jour le solde de la caisse
    $this->setSolde($this->getSolde() + $stock->getQuantity());

    // Vérifier si le solde est supérieur ou égal à zéro
    if ($this->getSolde() < 0) {
        throw new \Exception('Le solde de la caisse ne peut pas être négatif.');
    }

    return $this;
}

public function removeStock(Stock $stock): self
{
    if ($this->stocks->removeElement($stock)) {
        // set the owning side to null (unless already changed)
        if ($stock->getCaisse() === $this) {
            $stock->setCaisse(null);
        }
    }

    return $this;
}
public function fermerToutesLesCaisses(): void
{
    // Récupérer toutes les caisses de la base de données
    $caisses = $this->caisseRepository->findAll();

    // Fermer chaque caisse
    foreach ($caisses as $caisse) {
        $caisse->setFermee(true);
        $this->caisseRepository->getManager()->flush();
    }
}


/**
 * Get the value of fermee
 */
public function getFermee()
{
    return $this->fermee;
}

/**
 * Set the value of fermee
 *
 * @return  self
 */
public function setFermee($fermee)
{
    $this->fermee = $fermee;

    return $this;
}

}
