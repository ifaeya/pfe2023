<?php

namespace App\Entity;

use App\Repository\SocieteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;



#[ORM\Entity(repositoryClass: SocieteRepository::class)]

class Societe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $raisonsociale = null;

    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'UsersSociete')]
    private Collection $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRaisonsociale(): ?string
    {
        return $this->raisonsociale;
    }

    public function setRaisonsociale(string $raisonsociale): self
    {
        $this->raisonsociale = $raisonsociale;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addUsersSociete($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeUsersSociete($this);
        }

        return $this;
    }
    public function __toString()
{
    return strval($this->raisonsociale);
}
}
