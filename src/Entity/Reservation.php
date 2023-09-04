<?php

namespace App\Entity;
use App\Entity\User;
use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Devises $devises = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Typeoperation $type = null;

    #[ORM\Column]
    private ?float $montant = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?User $user = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $CreatedAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $confirmation = null;

    #[ORM\ManyToMany(targetEntity: Document::class, inversedBy: 'reservations')]
    private Collection $documents;

 
  
 

    public function __construct()
    {
        $this->CreatedAt = new \DateTimeImmutable();
        $this->documents = new ArrayCollection();

     
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getType(): ?Typeoperation
    {
        return $this->type;
    }

    public function setType(?Typeoperation $type): self
    {
        $this->type = $type;

        return $this;
    }



    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(float $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->CreatedAt;
    }

    public function setCreatedAt(\DateTimeInterface $CreatedAt): self
    {
        $this->CreatedAt = $CreatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->CreatedAt = $this->CreatedAt ?? new \DateTimeImmutable();
    }

    public function getConfirmation(): ?string
    {
        return $this->confirmation;
    }

    public function setConfirmation(?string $confirmation): static
    {
        $this->confirmation = $confirmation;

        return $this;
    }

    /**
     * @return Collection<int, Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): static
    {
        if (!$this->documents->contains($document)) {
            $this->documents->add($document);
        }

        return $this;
    }

    public function removeDocument(Document $document): static
    {
        $this->documents->removeElement($document);

        return $this;
    }





 
     

}