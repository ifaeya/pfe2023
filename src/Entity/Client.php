<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;

#[ORM\Entity(repositoryClass: ClientRepository::class)]



class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $numcin = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datecin = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $numpasseport = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datepasseport = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $datevalidpasport = null;

    #[ORM\OneToMany(mappedBy: 'client', targetEntity: Operation::class)]
    private Collection $operation;

  


    public function __construct()
    {
        $this->operation = new ArrayCollection();

    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }
    /**
     *    public function setNom(?string $nom ): self
{
    $this->nom = $nom;
    return $this;
}
     */

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getNumcin(): ?string
    {
        return $this->numcin;
    }

    public function setNumcin(string $numcin): self
    {
        $this->numcin = $numcin;

        return $this;
    }

    public function getDatecin(): ?\DateTimeInterface
    {
        return $this->datecin;
    }

    public function setDatecin(\DateTimeInterface $datecin): self
    {
        $this->datecin = $datecin;

        return $this;
    }

    public function getNumpasseport(): ?string
    {
        return $this->numpasseport;
    }

    public function setNumpasseport(string $numpasseport): self
    {
        $this->numpasseport = $numpasseport;

        return $this;
    }

    public function getDatepasseport(): ?\DateTimeInterface
    {
        return $this->datepasseport;
    }

    public function setDatepasseport(\DateTimeInterface $datepasseport): self
    {
        $this->datepasseport = $datepasseport;

        return $this;
    }

    public function getDatevalidpasport(): ?\DateTimeInterface
    {
        return $this->datevalidpasport;
    }

    public function setDatevalidpasport(\DateTimeInterface $datevalidpasport): self
    {
        $this->datevalidpasport = $datevalidpasport;

        return $this;
    }

    public function __toString(): string
    {
        return strval($this->nom . ' ' . $this->prenom);
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
            $operation->setClient($this);
        }

        return $this;
    }

    public function removeOperation(Operation $operation): self
    {
        if ($this->operation->removeElement($operation)) {
            // set the owning side to null (unless already changed)
            if ($operation->getClient() === $this) {
                $operation->setClient(null);
            }
        }

        return $this;
    }

   

  

}
