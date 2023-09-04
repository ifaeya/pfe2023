<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DevisesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DevisesRepository::class)]


class Devises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 100)]

    private ?string $code = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    #[ORM\Column(length: 255)]
    private ?string $abreviation = null;

    #[ORM\Column]
    private ?int $unite = null;

    #[ORM\Column(nullable: true, length: 255)]
    private ?string $image;

    #[ORM\OneToMany(mappedBy: 'devises', targetEntity: Coursdevises::class)]
    private Collection $devisecours;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Mouvement::class)]
    private Collection $mouvements;

    #[ORM\OneToMany(mappedBy: 'devises', targetEntity: Caisse::class)]
    private Collection $caisse;

    #[ORM\OneToMany(mappedBy: 'devise', targetEntity: Stock::class)]
    private Collection $stocks;

    #[ORM\OneToMany(mappedBy: 'devises', targetEntity: Reservation::class)]
    private Collection $reservations;



    public function __construct()
    {
        $this->devisecours = new ArrayCollection();
        $this->mouvements = new ArrayCollection();
        $this->caisse = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
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

    public function getAbreviation(): ?string
    {
        return $this->abreviation;
    }

    public function setAbreviation(string $abreviation): self
    {
        $this->abreviation = $abreviation;

        return $this;
    }

    public function getUnite(): ?int
    {
        return $this->unite;
    }

    public function setUnite(int $unite): self
    {
        $this->unite = $unite;

        return $this;
    }
    public function getImage(): ?string
    {
        return $this->image;
    }


    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }


    /**
     * @return Collection<int, Coursdevises>
     */
    public function getDevisecours(): Collection
    {
        return $this->devisecours;
    }
    public function getValeurAchat(): ?float
    {
        $coursDevise = $this->getDevisecours()->first();
        return $coursDevise ? $coursDevise->getValeurAchat() : null;
    }

    public function getValeurVente(): ?float
    {
        $coursDevise = $this->getDevisecours()->first();
        return $coursDevise ? $coursDevise->getValeurVente() : null;
    }


    public function getTauxDeChange(): ?float
    {
        $coursDevise = $this->devisecours->first();
        if ($coursDevise && $coursDevise->getValeurAchat() && $coursDevise->getValeurVente()) {
            return $coursDevise->getValeurVente() / $coursDevise->getValeurAchat();
        }
        return null;
    }
    public function addDevisecour(Coursdevises $devisecour): self
    {
        if (!$this->devisecours->contains($devisecour)) {
            $this->devisecours->add($devisecour);
            $devisecour->setDevises($this);
        }

        return $this;
    }

    public function removeDevisecour(Coursdevises $devisecour): self
    {
        if ($this->devisecours->removeElement($devisecour)) {
            // set the owning side to null (unless already changed)
            if ($devisecour->getDevises() === $this) {
                $devisecour->setDevises(null);
            }
        }

        return $this;
    }

public function __toString()
{
    return strval($this->id);
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
    if (!$this->mouvements->contains($mouvement)) {
        $this->mouvements->add($mouvement);
        $mouvement->setDevise($this);
    }

    return $this;
}

public function removeMouvement(Mouvement $mouvement): self
{
    if ($this->mouvements->removeElement($mouvement)) {
        // set the owning side to null (unless already changed)
        if ($mouvement->getDevise() === $this) {
            $mouvement->setDevise(null);
        }
    }

    return $this;
}



/**
 * @return Collection<int, Caisse>
 */
public function getCaisse(): Collection
{
    return $this->caisse;
}

public function addCaisse(Caisse $caisse): self
{
    if (!$this->caisse->contains($caisse)) {
        $this->caisse->add($caisse);
        $caisse->setDevises($this);
    }

    return $this;
}

public function removeCaisse(Caisse $caisse): self
{
    if ($this->caisse->removeElement($caisse)) {
        // set the owning side to null (unless already changed)
        if ($caisse->getDevises() === $this) {
            $caisse->setDevises(null);
        }
    }

    return $this;
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
    if (!$this->stocks->contains($stock)) {
        $this->stocks->add($stock);
        $stock->setDevise($this);
    }

    return $this;
}

public function removeStock(Stock $stock): self
{
    if ($this->stocks->removeElement($stock)) {
        // set the owning side to null (unless already changed)
        if ($stock->getDevise() === $this) {
            $stock->setDevise(null);
        }
    }

    return $this;
}





public function convertToTND(float $montant): ?float
{
    $valeurachat = $this->getValeurAchat();

    if ($valeurachat === null) {
        return null;
    }

    return $montant * $valeurachat;
}

/**
 * @return Collection<int, Reservation>
 */
public function getReservations(): Collection
{
    return $this->reservations;
}

public function addReservation(Reservation $reservation): self
{
    if (!$this->reservations->contains($reservation)) {
        $this->reservations->add($reservation);
        $reservation->setDevises($this);
    }

    return $this;
}

public function removeReservation(Reservation $reservation): self
{
    if ($this->reservations->removeElement($reservation)) {
        // set the owning side to null (unless already changed)
        if ($reservation->getDevises() === $this) {
            $reservation->setDevises(null);
        }
    }

    return $this;
}





}
