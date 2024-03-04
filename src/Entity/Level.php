<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\LevelRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LevelRepository::class)]
#[ApiResource]
class Level
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $level = 1;

    #[ORM\Column]
    private ?int $requiredXp = 100;

    #[ORM\Column]
    private ?int $actualXp = 0;

    #[ORM\OneToOne(mappedBy: 'inventory', cascade: ['persist', 'remove'])]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getRequiredXp(): ?int
    {
        return $this->requiredXp;
    }

    public function setRequiredXp(int $requiredXp): static
    {
        $this->requiredXp = $requiredXp;

        return $this;
    }

    public function getActualXp(): ?int
    {
        return $this->actualXp;
    }

    public function setActualXp(int $actualXp): static
    {
        $this->actualXp = $actualXp;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        // set the owning side of the relation if necessary
        if ($user->getLevel() !== $this) {
            $user->setLevel($this);
        }

        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}
