<?php

namespace App\Entity;

use App\Repository\TypeoperationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

#[ORM\Entity(repositoryClass: TypeoperationRepository::class)]

class Typeoperation
{
    public const TYPE_ACHAT = 'achat';
    public const TYPE_VENTE = 'vente';
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private ?string $Libelle = null;

    #[ORM\OneToMany(mappedBy: 'typeoperation', targetEntity: Operation::class)]
    private Collection $operation;

    #[ORM\OneToMany(mappedBy: 'type', targetEntity: Reservation::class)]
    private Collection $reservations;

    public function __construct()
    {
        $this->operation = new ArrayCollection();
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->Libelle;
    }

    public function setLibelle(string $Libelle): self
    {
        $this->Libelle = $Libelle;

        return $this;
    }

    /**
     * @return Collection<int, Operation>
     */
    public function getOperation(): Collection
    {
        return $this->operation;
    }

    public function addOperation(Operation $operation): self
    {
        if (!$this->operation->contains($operation)) {
            $this->operation->add($operation);
            $operation->setTypeoperation($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operation->removeElement($operation)) {
            // set the owning side to null (unless already changed)
            if ($operation->getTypeoperation() === $this) {
                $operation->setTypeoperation(null);
            }
        }

        return $this;
    }
    public function __toString()
    {
        return strval($this->Libelle);
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
            $reservation->setType($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): self
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getType() === $this) {
                $reservation->setType(null);
            }
        }

        return $this;
    }
}
