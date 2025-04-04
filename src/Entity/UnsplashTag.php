<?php

namespace App\Entity;

use App\Repository\UnsplashTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UnsplashTagRepository::class)]
class UnsplashTag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $term = null;

    #[ORM\Column]
    private ?int $totalPages = null;

    #[ORM\Column]
    private ?int $currentPage = null;

    /**
     * @var Collection<int, UnsplashImage>
     */
    #[ORM\OneToMany(mappedBy: 'unsplashTag', targetEntity: UnsplashImage::class)]
    private Collection $images;

    #[ORM\Column(options: ['default' => false])]
    private bool $fullyLStored = false;

    public function __construct()
    {
        $this->images = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTerm(): ?string
    {
        return $this->term;
    }

    public function setTerm(string $term): static
    {
        $this->term = $term;

        return $this;
    }

    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }

    public function setTotalPages(int $totalPages): static
    {
        $this->totalPages = $totalPages;

        return $this;
    }

    public function getCurrentPage(): ?int
    {
        return $this->currentPage;
    }

    public function setCurrentPage(int $currentPage): static
    {
        $this->currentPage = $currentPage;

        return $this;
    }

    /**
     * @return Collection<int, UnsplashImage>
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(UnsplashImage $image): static
    {
        if (!$this->images->contains($image)) {
            $this->images->add($image);
            $image->setUnsplashTag($this);
        }

        return $this;
    }

    public function removeImage(UnsplashImage $image): static
    {
        if ($this->images->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getUnsplashTag() === $this) {
                $image->setUnsplashTag(null);
            }
        }

        return $this;
    }

    public function __toString(){
        return $this->term;
    }

    public function isFullyLStored(): bool
    {
        return $this->fullyLStored;
    }

    public function setFullyLStored(bool $fullyLStored): static
    {
        $this->fullyLStored = $fullyLStored;

        return $this;
    }
}
