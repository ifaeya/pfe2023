<?php

namespace App\Entity;

use App\Repository\CoursdevisesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\EntityManagerInterface;

#[ORM\Entity(repositoryClass: CoursdevisesRepository::class)]


class Coursdevises
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?float $valeurachat = null;

    #[ORM\Column]
    private ?float $valeurvente = null;

    #[ORM\ManyToOne(inversedBy: 'devisecours')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Devises $devises = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'cours', targetEntity: ArchiveCoursdevises::class)]
    private Collection $archiveCoursdevises;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->createdAt = new \DateTimeImmutable();
        $this->archiveCoursdevises = new ArrayCollection();
    }


    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }





    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValeurachat(): ?float
    {
        return $this->valeurachat;
    }

    public function setValeurachat(float $valeurachat): self
    {
        $this->valeurachat = $valeurachat;
        $this->preUpdate(); // appel à la méthode preUpdate pour mettre à jour updatedAt

        return $this;
    }

    public function getValeurvente(): ?float
    {
        return $this->valeurvente;
    }

    public function setValeurvente(float $valeurvente): self
    {
        $this->valeurvente = $valeurvente;
        $this->preUpdate(); // appel à la méthode preUpdate pour mettre à jour updatedAt

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

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->createdAt = $this->createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $this->updatedAt ?? new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function __toString()
    {
        return strval($this->id);
    }
    public function getMarge(): ?float
    {
        if ($this->valeurvente && $this->valeurachat) {
            $marge = (($this->valeurvente - $this->valeurachat) / $this->valeurvente) * 100;
            return round(abs($marge), 2);
        }
        return null;
    }
public function getTauxDeChange(): ?float
{
    if ($this->valeurachat && $this->valeurvente) {
        return $this->valeurvente / $this->valeurachat;
    }
    return null;
}
public function getValeurachatTND(float $unite = 1): ?float
{
    if ($this->valeurachat === null) {
        return null;
    }

    return $this->valeurachat * $unite;
}

/**
 * @return Collection<int, ArchiveCoursdevises>
 */
public function getArchiveCoursdevises(): Collection
{
    return $this->archiveCoursdevises;
}

public function addArchiveCoursdevise(ArchiveCoursdevises $archiveCoursdevise): self
{
    if (!$this->archiveCoursdevises->contains($archiveCoursdevise)) {
        $this->archiveCoursdevises->add($archiveCoursdevise);
        $archiveCoursdevise->setCours($this);
    }

    return $this;
}

public function removeArchiveCoursdevise(ArchiveCoursdevises $archiveCoursdevise): self
{
    if ($this->archiveCoursdevises->removeElement($archiveCoursdevise)) {
        // set the owning side to null (unless already changed)
        if ($archiveCoursdevise->getCours() === $this) {
            $archiveCoursdevise->setCours(null);
        }
    }

    return $this;
}

#[ORM\PreUpdate]
public function updateProfitAndArchive(PreUpdateEventArgs $eventArgs): void
{
    // Get the previous values from the change set
    $oldValeurachat = $eventArgs->getOldValue('valeurachat');
    $oldValeurvente = $eventArgs->getOldValue('valeurvente');

    // Check if valeurachat or valeurvente has changed
    if ($oldValeurachat !== $this->valeurachat || $oldValeurvente !== $this->valeurvente) {
        // Create a new ArchiveCoursdevises record and set its properties
        $archiveCoursdevises = new ArchiveCoursdevises();
        $archiveCoursdevises->setCours($this);
        $archiveCoursdevises->setDateArchivage(new \DateTime()); // Set the archive date
        $archiveCoursdevises->setValeurachat($oldValeurachat); // Archive the previous value
        $archiveCoursdevises->setValeurvente($oldValeurvente); // Archive the previous value

        // Persist the new archive record
        $this->entityManager->persist($archiveCoursdevises);

        // Update the updatedAt timestamp
        $this->updatedAt = new \DateTimeImmutable();

        // Flush the changes to the database
        $this->entityManager->flush();
    }
}

}
