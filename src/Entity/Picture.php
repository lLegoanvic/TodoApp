<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\PictureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: PictureRepository::class)]
#[ApiResource]
#[ApiResource(order: ['pkmId' => 'ASC'])]
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['inventory:get'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['inventory:get'])]
    private ?string $pkmpicture = null;



    #[ORM\ManyToOne(inversedBy: 'pictures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Inventory $inventory = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['inventory:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['inventory:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'pictures')]
    #[Groups(['inventory:get'])]
    private ?Frame $frame = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['inventory:get'])]
    private ?int $quantity = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['inventory:get'])]
    private ?int $pkmId = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['inventory:get'])]
    private ?string $pkmName = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPkmpicture(): ?string
    {
        return $this->pkmpicture;
    }

    public function setPkmpicture(string $pkmpicture): static
    {
        $this->pkmpicture = $pkmpicture;

        return $this;
    }

  

    public function getInventory(): ?Inventory
    {
        return $this->inventory;
    }

    public function setInventory(?Inventory $inventory): static
    {
        $this->inventory = $inventory;

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

    public function getFrame(): ?Frame
    {
        return $this->frame;
    }

    public function setFrame(?Frame $frame): static
    {
        $this->frame = $frame;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPkmId(): ?int
    {
        return $this->pkmId;
    }

    public function setPkmId(?int $pkmId): static
    {
        $this->pkmId = $pkmId;

        return $this;
    }

    public function getPkmName(): ?string
    {
        return $this->pkmName;
    }

    public function setPkmName(?string $pkmName): static
    {
        $this->pkmName = $pkmName;

        return $this;
    }
}
