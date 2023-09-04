<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $libelle = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $path = null;

    #[ORM\ManyToMany(targetEntity: Operation::class, inversedBy: 'documents' , cascade: ["persist"])]
    private Collection $operation;

    #[ORM\ManyToMany(targetEntity: Reservation::class, mappedBy: 'documents')]
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
        return $this->libelle;
    }

    public function setLibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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
      //  if (!$this->operation->contains($operation)) {
        //    $this->operation->add($operation);
        //}
        if (!$this->operation->contains($operation)) {
            $this->operation[] = $operation;
            $operation->addDocument($this);
        }
    
        return $this;
       
    }

    public function removeOperation(Operation $operation): self
    {
        $this->operation->removeElement($operation);

        return $this;
    }
    public function __toString()
{
return strval($this->id);
}

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->addDocument($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            $reservation->removeDocument($this);
        }

        return $this;
    }

 
    public function isImage(): bool
    {
        return in_array($this->getExtension(), ['jpg', 'jpeg', 'png', 'gif']);
    }

    public function isPDF(): bool
    {
        return $this->getExtension() === 'pdf';
    }

    private function getExtension(): string
    {
        return pathinfo($this->libelle, PATHINFO_EXTENSION);
    }



    /**
     * Get the value of path
     */ 
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the value of path
     *
     * @return  self
     */ 
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }
}
