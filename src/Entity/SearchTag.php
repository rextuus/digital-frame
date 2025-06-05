<?php

namespace App\Entity;

use App\Repository\SearchTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use \App\Service\Unsplash\TagVariant;

#[ORM\Entity(repositoryClass: SearchTagRepository::class)]
class SearchTag
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
    #[ORM\OneToMany(mappedBy: 'searchTag', targetEntity: UnsplashImage::class)]
    private Collection $unsplashImages;

    #[ORM\Column(options: ['default' => false])]
    private bool $fullyLStored = false;

    /**
     * @var Collection<int, DisplateImage>
     */
    #[ORM\OneToMany(mappedBy: 'searchTag', targetEntity: DisplateImage::class)]
    private Collection $displateImages;

    #[ORM\Column(nullable: false, enumType: TagVariant::class, options: ['default' => TagVariant::UNSPLASH])]
    private TagVariant $variant = TagVariant::UNSPLASH;

    #[ORM\Column(nullable: false, options: ['default' => false])]
    private bool $collectingInProgress = false;

    public function __construct()
    {
        $this->unsplashImages = new ArrayCollection();
        $this->displateImages = new ArrayCollection();
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
    public function getUnsplashImages(): Collection
    {
        return $this->unsplashImages;
    }

    public function addImage(UnsplashImage $image): static
    {
        if (!$this->unsplashImages->contains($image)) {
            $this->unsplashImages->add($image);
            $image->setSearchTag($this);
        }

        return $this;
    }

    public function removeImage(UnsplashImage $image): static
    {
        if ($this->unsplashImages->removeElement($image)) {
            // set the owning side to null (unless already changed)
            if ($image->getSearchTag() === $this) {
                $image->setSearchTag(null);
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

    /**
     * @return Collection<int, DisplateImage>
     */
    public function getDisplateImages(): Collection
    {
        return $this->displateImages;
    }

    public function addDisplateImage(DisplateImage $displateImage): static
    {
        if (!$this->displateImages->contains($displateImage)) {
            $this->displateImages->add($displateImage);
            $displateImage->setSearchTag($this);
        }

        return $this;
    }

    public function removeDisplateImage(DisplateImage $displateImage): static
    {
        if ($this->displateImages->removeElement($displateImage)) {
            // set the owning side to null (unless already changed)
            if ($displateImage->getSearchTag() === $this) {
                $displateImage->setSearchTag(null);
            }
        }

        return $this;
    }

    public function getVariant(): TagVariant
    {
        return $this->variant;
    }

    public function setVariant(TagVariant $variant): SearchTag
    {
        $this->variant = $variant;
        return $this;
    }

    public function isCollectingInProgress(): ?bool
    {
        return $this->collectingInProgress;
    }

    public function setCollectingInProgress(bool $collectingInProgress): static
    {
        $this->collectingInProgress = $collectingInProgress;

        return $this;
    }
}
