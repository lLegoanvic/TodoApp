<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\FrameRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FrameRepository::class)]
#[ApiResource]
class Frame
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToMany(targetEntity: Picture::class, mappedBy: 'frame')]
    private Collection $pictures;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $frameName = null;

    #[ORM\Column]
    private ?int $codeFrame = null;

    public function __construct()
    {
        $this->pictures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $picture->setFrame($this);
        }

        return $this;
    }

    public function removePicture(Picture $picture): static
    {
        if ($this->pictures->removeElement($picture)) {
            // set the owning side to null (unless already changed)
            if ($picture->getFrame() === $this) {
                $picture->setFrame(null);
            }
        }

        return $this;
    }

    public function getFrameName(): ?string
    {
        return $this->frameName;
    }

    public function setFrameName(?string $frameName): static
    {
        $this->frameName = $frameName;

        return $this;
    }

    public function getCodeFrame(): ?int
    {
        return $this->codeFrame;
    }

    public function setCodeFrame(int $codeFrame): static
    {
        $this->codeFrame = $codeFrame;

        return $this;
    }
}
