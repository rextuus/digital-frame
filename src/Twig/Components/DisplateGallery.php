<?php

declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\DisplateImage;
use App\Entity\SearchTag;
use App\Service\Displate\DisplateImageService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class DisplateGallery
{
    use DefaultActionTrait;

    private const PER_PAGE = 20;

    #[LiveProp]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public ?SearchTag $selectedTag = null;

    public function __construct(private readonly DisplateImageService $displateImageService)
    {
    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        return $this->displateImageService->getImageCountByTagWithPagination(
            self::PER_PAGE,
            $this->page,
            $this->selectedTag
        );
    }

    /**
     * @return array<DisplateImage>
     */
    public function getImages(): array
    {
        return $this->displateImageService->getImagesByTagWithPagination(
            self::PER_PAGE,
            ($this->page - 1) * self::PER_PAGE,
            $this->selectedTag
        );
    }

    /**
     * @return array<SearchTag>
     */
    public function getTags(): array
    {
        return $this->displateImageService->getExistingDisplateTags();
    }
}
