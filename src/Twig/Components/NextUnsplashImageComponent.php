<?php

namespace App\Twig\Components;

use App\Entity\UnsplashImage;
use App\Repository\UnsplashImageRepository;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class NextUnsplashImageComponent
{
    use DefaultActionTrait;

    private const IMAGES_PER_PAGE = 5;

    #[LiveProp(writable: true)]
    public ?int $desireCount = 1;

    public function __construct(
        private readonly FrameConfigurationService $frameConfigurationService,
        private readonly UnsplashImageRepository $unsplashImageRepository,
    ) {
    }

    /**
     * @return array<UnsplashImage>
     */
    #[LiveAction]
    #[LiveListener('imageDisplayed')]
    public function getNextImages(): array
    {
        $currentTag = $this->frameConfigurationService->getCurrentTag();
        $this->desireCount++;
        $test = rand(1, 4);

        return [$this->unsplashImageRepository->findByNextUnseenByTag($currentTag, self::IMAGES_PER_PAGE)[$test]];
    }

    public function getButtonCss(): void
    {
        $this->getNextImages();
    }
}
