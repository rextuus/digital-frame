<?php

namespace App\Twig\Components;

use App\Repository\ArtsyImageRepository;
use App\Service\Artsy\ArtsyService;
use App\Service\Artsy\Category;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Gallery
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public bool $onlyPaintings = false;

    private const IMAGES_PER_PAGE = 5;

    public function __construct(
        private readonly FrameConfigurationService $frameConfigurationService,
        private readonly ArtsyImageRepository $imageRepository
    )
    {
    }

    public function getImages(): array
    {
        $categories = [Category::PAINTING];
        if (!$this->onlyPaintings){
            $categories = Category::cases();
        }

        return $this->imageRepository->findPaginatedImages($this->page, self::IMAGES_PER_PAGE, $categories);
    }

    public function hasMorePages(): bool
    {
        $condition = ['category' => Category::PAINTING];
        if (!$this->onlyPaintings){
            $condition = [];
        }

        $totalImages = $this->imageRepository->count($condition);
        return ($this->page * self::IMAGES_PER_PAGE) < $totalImages;
    }

    public function getTotalPages(): int
    {
        $condition = ['category' => Category::PAINTING];
        if (!$this->onlyPaintings){
            $condition = [];
        }

        return ceil($this->imageRepository->count($condition) / self::IMAGES_PER_PAGE);
    }

    public function hasPreviousPages(): bool
    {
        return $this->page > 1;
    }

    #[LiveAction]
    public function loadMore(): void
    {
        $this->page++;
    }

    #[LiveAction]
    public function loadBefore(): void
    {
        $this->page--;
    }

    #[LiveAction]
    public function display(#[LiveArg] int $id): void
    {
        $this->frameConfigurationService->setNextImageId($id);
        $this->frameConfigurationService->setNext(true);
    }

    #[LiveAction]
    public function toggleOnlyPaintings(): void
    {
        $this->onlyPaintings = !$this->onlyPaintings;
        $this->page = 1;
    }
}
