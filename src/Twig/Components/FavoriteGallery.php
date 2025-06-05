<?php

namespace App\Twig\Components;

use App\Entity\Favorite;
use App\Repository\FavoriteRepository;
use App\Service\Artsy\Category;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class FavoriteGallery
{
    use DefaultActionTrait;

    private const IMAGES_PER_PAGE = 4;

    #[LiveProp(writable: true)]
    public int $page = 1;

    #[LiveProp(writable: true)]
    public string $sort = 'ASC';

    #[LiveProp(writable: true)]
    public ?int $totalCount = 1;

    /**
     * @var array<bool>
     */
    #[LiveProp(writable: true)]
    public array $modesEnabled = [];

    public function __construct(
        private readonly FrameConfigurationService $frameConfigurationService,
        private readonly FavoriteRepository $favoriteRepository,
    )
    {
        $availableModes = $this->favoriteRepository->getPresentModes();
        $availableModes = array_map(fn(array $mode) => $mode[array_key_first($mode)]->name, $availableModes);

        foreach (DisplayMode::cases() as $mode) {
            $this->modesEnabled[$mode->name] = true;
            if (!in_array($mode->name, $availableModes)) {
                $this->modesEnabled[$mode->name] = false;
            }
        }
    }

    /**
     * @return array<Category>
     */
    private function getEnabledModes(): array
    {
        $choices = [];
        foreach ($this->modesEnabled as $mode => $enabled) {
            if ($enabled) {
                $choices[] = DisplayMode::fromName($mode);
            }
        }

        return $choices;
    }

    /**
     * @return array<Favorite>
     */
    public function getImages(): array
    {
        $totalImages = $this->favoriteRepository->countFavorites($this->getEnabledModes());
        $this->totalCount = $totalImages;

        return $this->favoriteRepository->findPaginatedFavorites(
            $this->page,
            self::IMAGES_PER_PAGE,
            $this->getEnabledModes(),
            $this->sort,
        );
    }

    public function hasMorePages(): bool
    {
        $totalImages = $this->favoriteRepository->countFavorites($this->getEnabledModes());

        return ($this->page * self::IMAGES_PER_PAGE) < $totalImages;
    }

    public function getTotalPages(): int
    {
        $totalImages = $this->favoriteRepository->countFavorites($this->getEnabledModes());

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
    public function toggleMode(#[LiveArg] string $category): void
    {
        $this->modesEnabled[$category] = !$this->modesEnabled[$category];
        $this->page = 1;
    }

    #[LiveAction]
    public function setPage(#[LiveArg] int $page): void
    {
        if ($page > 0 && $page <= $this->getTotalPages()) {
            $this->page = $page;
        }
    }

    public function getModes(): array
    {
        $modes = [];
        foreach (DisplayMode::cases() as $mode) {
            $modes[] = $mode->name;
        }

        return $modes;
    }

    public function isModeEnabled(string $mode): bool
    {
        return $this->modesEnabled[$mode];
    }

    public function getButtonCssForMode(string $modeIdent): string
    {
        $mode = DisplayMode::fromName($modeIdent);

        $isEnabled = $this->isModeEnabled($modeIdent);
        if ($isEnabled) {
            return $mode->getFavoriteColorStyle();
        }

        return 'bg-default';
    }

    public function getFontAwesomeClassForMode(string $modeIdent): string
    {
        $mode = DisplayMode::fromName($modeIdent);

        return $mode->getFontAwesomeClass();
    }
}
