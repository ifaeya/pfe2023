<?php

namespace App\Entity;

use App\Repository\ArchiveCoursdevisesRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: ArchiveCoursdevisesRepository::class)]
class ArchiveCoursdevises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }


    
    #[ORM\ManyToOne(inversedBy: 'archiveCoursdevises')]
    private ?Coursdevises $cours = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateArchivage = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $valeurachat = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $valeurvente = null;
    
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $profit = null;

    // ... existing methods

    // The profit calculation logic
    private function calculateProfit(): ?string
    {
        if ($this->valeurvente && $this->valeurachat) {
            $marge = (($this->valeurvente - $this->valeurachat) / $this->valeurvente) * 100;
            return round(abs($marge), 2);
        }
        return null;
    }

    // PrePersist and PreUpdate event listeners to update the profit before saving
    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateProfit(): void
    {
        $this->profit = $this->calculateProfit();
    }

    
    public function getProfit(): ?Coursdevises
    {
        return $this->profit;
    }

    public function setProfit(?Coursdevises $profit): self
    {
        $this->profit = $profit;

        return $this;
    }
    public function getCours(): ?Coursdevises
    {
        return $this->cours;
    }

    public function setCours(?Coursdevises $cours): self
    {
        $this->cours = $cours;

        return $this;
    }

    public function getDateArchivage(): ?\DateTimeInterface
    {
        return $this->dateArchivage;
    }

    public function setDateArchivage(\DateTimeInterface $dateArchivage): self
    {
        $this->dateArchivage = $dateArchivage;

        return $this;
    }

    public function getValeurachat(): ?string
    {
        return $this->valeurachat;
    }

    public function setValeurachat(string $valeurachat): self
    {
        $this->valeurachat = $valeurachat;

        return $this;
    }

    public function getValeurvente(): ?string
    {
        return $this->valeurvente;
    }

    public function setValeurvente(string $valeurvente): self
    {
        $this->valeurvente = $valeurvente;

        return $this;
    }

}


