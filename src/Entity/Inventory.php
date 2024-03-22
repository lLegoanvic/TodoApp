<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\InventoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: InventoryRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['inventory:get']]  // group de lecture de l'entité
    // appliqué a chaque property que l'api dois return
    // si lecture de sous entité comme booster il faut ajouter le groupe de lecture du parent
    // a chaque prop de l'enfant ( attention de pas le mettre sur le parents en question dans l'enfant ) 
    // le nom inventory:get c'est juste pour s'y retrouver tu peux l'appeler comme tu veux genre ['groups' => ['OUILO']
)]
class Inventory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['inventory:get'])]
    private ?int $id = null;



    #[ORM\Column(nullable: true)]
    #[Groups(['inventory:get'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['inventory:get'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(targetEntity: Booster::class, mappedBy: 'inventory')]
    #[Groups(['inventory:get'])]
    private Collection $boosters;

    #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'inventory')]
    #[Groups(['inventory:get'])]
    private Collection $pictures;

    #[ORM\OneToOne(mappedBy: 'inventory', cascade: ['persist', 'remove'])]
    #[Groups(['inventory:get'])]
    private ?User $userInventory = null;

    public function __construct()
    {
        $this->boosters = new ArrayCollection();
        $this->pictures = new ArrayCollection();
//        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Booster>
     */
    public function getBoosters(): Collection
    {
        return $this->boosters;
    }

    public function addBooster(Booster $booster): static
    {
        if (!$this->boosters->contains($booster)) {
            $this->boosters->add($booster);
            $booster->setInventory($this);
        }

        return $this;
    }

    public function removeBooster(Booster $booster): static
    {
        if ($this->boosters->removeElement($booster)) {
            // set the owning side to null (unless already changed)
            if ($booster->getInventory() === $this) {
                $booster->setInventory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Picture>
     */
    public function getPictures(): Collection
    {
        return $this->pictures;
    }

    public function addPicture(Picture $picture): static
    {
        if (!$this->pictures->contains($picture)) {
            $this->pictures->add($picture);
            $picture->setInventory($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getInventory() === $this) {
                $picture->setInventory(null);
            }
        }

        return $this;
    }

    public function getUserInventory(): ?User
    {
        return $this->userInventory;
    }

    public function setUserInventory(User $userInventory): static
    {
        // set the owning side of the relation if necessary
        if ($userInventory->getInventory() !== $this) {
            $userInventory->setInventory($this);
        }

        $this->userInventory = $userInventory;

        return $this;
    }
}
