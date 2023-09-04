<?php

namespace App\Entity;

use App\Repository\MouvementRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: MouvementRepository::class)]

class Mouvement
{
   
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    #[Assert\Choice(choices: ['E', 'S'])]

    private ?string $SensMouvement = null;

    #[ORM\Column]
    private ?float $valeurDevise = null;

    #[ORM\ManyToOne(inversedBy: 'mouvements')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Operation $operation = null;

    #[ORM\ManyToOne(inversedBy: 'mouvements')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]

    private ?Devises $devise = null;

    #[ORM\ManyToOne(inversedBy: 'mouvements')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?Caisse $caisse = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSensMouvement(): ?string
    {
        return $this->SensMouvement;
    }

    public function setSensMouvement(string $SensMouvement): self
    {
        $this->SensMouvement = $SensMouvement;

        return $this;
    }

    public function getValeurDevise(): ?float
    {
        return $this->valeurDevise;
    }

    public function setValeurDevise(float $valeurDevise): self
    {
        $this->valeurDevise = $valeurDevise;

        return $this;
    }

    public function getOperation(): ?Operation
    {
        return $this->operation;
    }

    public function setOperation(?Operation $operation): self
    {
        $this->operation = $operation;

        return $this;
    }

    public function getDevise(): ?Devises
    {
        return $this->devise;
    }

    public function setDevise(?Devises $devise): self
    {
        $this->devise = $devise;

        return $this;
    }

    public function getCaisse(): ?Caisse
    {
        return $this->caisse;
    }

    public function setCaisse(?Caisse $caisse): self
    {
        $this->caisse = $caisse;

        return $this;
    }
    public function __toString()
{
return strval($this->SensMouvement);
}


}
