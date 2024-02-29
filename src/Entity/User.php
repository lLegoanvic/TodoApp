<?php

namespace App\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource]
class User implements \Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface
{


    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private \DateTimeInterface $createdAt;


    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Level $level = null;

    #[ORM\OneToMany(targetEntity: QuestProgress::class, mappedBy: 'user')]
    private Collection $questProgress;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'user')]
    private Collection $tasks;

    #[ORM\OneToOne(mappedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Inventory $inventory = null;

    #[ORM\Column(length: 255)]
    private ?string $roles = null;

    public function __construct()
    {
        $this->questProgress = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }



    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }


    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function setLevel(Level $level): static
    {
        // set the owning side of the relation if necessary
        if ($level->getUser() !== $this) {
            $level->setUser($this);
        }

        $this->level = $level;

        return $this;
    }

    /**
     * @return Collection<int, QuestProgress>
     */
    public function getQuestProgress(): Collection
    {
        return $this->questProgress;
    }

    public function addQuestProgress(QuestProgress $questProgress): static
    {
        if (!$this->questProgress->contains($questProgress)) {
            $this->questProgress->add($questProgress);
            $questProgress->setUser($this);
        }

        return $this;
    }

    public function removeQuestProgress(QuestProgress $questProgress): static
    {
        if ($this->questProgress->removeElement($questProgress)) {
            // set the owning side to null (unless already changed)
            if ($questProgress->getUser() === $this) {
                $questProgress->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): static
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): static
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(Inventory $inventory): static
    {
        // set the owning side of the relation if necessary
        if ($inventory->getUser() !== $this) {
            $inventory->setUser($this);
        }

        $this->inventory = $inventory;

        return $this;
    }

    public function getRoles(): ?string
    {
        return $this->roles;
    }

    public function setRoles(string $roles): static
    {
        $this->roles = $roles;

        return $this;
    }


}
