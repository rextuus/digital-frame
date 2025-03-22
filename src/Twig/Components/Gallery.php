<?php

namespace App\Twig\Components;

use App\Repository\ArtsyImageRepository;
use App\Service\Artsy\ArtworkDimensionFilter;
use App\Service\Artsy\Category;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class Gallery
{
    use DefaultActionTrait;

    private const IMAGES_PER_PAGE = 4;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public ArtworkDimensionFilter $orientation = ArtworkDimensionFilter::ALL;

    #[LiveProp(writable: true)]
    public bool $sculptures = false;

    #[LiveProp(writable: true)]
    public string $sort = 'ASC';

    /**
     * @var array<bool>
     */
    #[LiveProp(writable: true)]
    public array $categoriesEnabled = [];

    public function __construct(
        private readonly FrameConfigurationService $frameConfigurationService,
        private readonly ArtsyImageRepository $imageRepository,
        private readonly RequestStack $requestStack
    ) {
        foreach (Category::cases() as $category) {
            if ($category !== Category::UNKNOWN) {
                $this->categoriesEnabled[$category->name] = $category === Category::PAINTING;
            }
        }
    }

    /**
     * @return array<Category>
     */
    private function getEnabledCategories(): array
    {
        $choices = [];
        foreach ($this->categoriesEnabled as $category => $enabled) {
            if ($enabled) {
                $choices[] = Category::fromName($category);
            }
        }

        return $choices;
    }

    public function getImages(): array
    {
        return $this->imageRepository->findPaginatedImages(
            $this->page,
            self::IMAGES_PER_PAGE,
            $this->getEnabledCategories(),
            $this->sort,
            $this->orientation
        );
    }

    public function hasMorePages(): bool
    {
        $totalImages = count(
            $this->imageRepository->findPaginatedImages(
                1,
                1000000,
                $this->getEnabledCategories(),
                $this->sort,
                $this->orientation
            )
        );
        return ($this->page * self::IMAGES_PER_PAGE) < $totalImages;
    }

    public function getTotalPages(): int
    {
        $totalImages = count(
            $this->imageRepository->findPaginatedImages(
                1,
                1000000,
                $this->getEnabledCategories(),
                $this->sort,
                $this->orientation
            )
        );
        return ceil($totalImages / self::IMAGES_PER_PAGE);
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
    public function toggleOrientation(): void
    {
        if ($this->orientation === ArtworkDimensionFilter::ALL) {
            $this->orientation = ArtworkDimensionFilter::PORTRAIT;
        } elseif ($this->orientation === ArtworkDimensionFilter::PORTRAIT) {
            $this->orientation = ArtworkDimensionFilter::LANDSCAPE;
        } elseif ($this->orientation === ArtworkDimensionFilter::LANDSCAPE) {
            $this->orientation = ArtworkDimensionFilter::ALL;
        }

        $this->page = 1;

        if ($this->sort === 'ASC') {
            $this->sort = 'DESC';
        } else {
            $this->sort = 'ASC';
        }
    }

    public function getOrientation(): string
    {
        return $this->orientation->value;
    }

    #[LiveAction]
    public function toggleCategory(#[LiveArg] string $category): void
    {
        $this->categoriesEnabled[$category] = !$this->categoriesEnabled[$category];
        $this->page = 1;

        if ($this->sort === 'ASC') {
            $this->sort = 'DESC';
        } else {
            $this->sort = 'ASC';
        }
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        if ($page > 0 && $page <= $this->getTotalPages()) {
            $this->page = $page;
        }
    }

    public function getCategories(): array
    {
        $categories = [];
        foreach (Category::cases() as $category) {
            if ($category !== Category::UNKNOWN) {
                $categories[] = $category->name;
            }
        }

        return $categories;
    }

    public function isCategoryEnabled(string $category): bool
    {
        return $this->categoriesEnabled[$category];
    }

    public function getButtonCssForCategory(string $category): string
    {
        return $this->isCategoryEnabled($category) ? 'btn-primary' : 'btn-secondary';
    }

    public function getFontAwesomeClassForCategory(string $categoryIdent): string
    {
        $category = Category::fromName($categoryIdent);

        return $category->getFontAwesomeClass();
    }

    public function getFontAwesomeClassForCurrentOrientation(): string
    {
        return $this->orientation->getFontAwesomeClass();
    }
}
