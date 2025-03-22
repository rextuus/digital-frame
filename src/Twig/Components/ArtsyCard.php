<?php

namespace App\Twig\Components;

use App\Entity\ArtsyImage;
use App\Repository\ArtsyImageRepository;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ArtsyCard
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ArtsyImage $image;

    #[LiveProp]
    public bool $wasDisplayed = false;

    public function __construct(
        private readonly FrameConfigurationService $frameConfigurationService,
        private readonly RequestStack $requestStack
    ) {
    }

    #[LiveAction]
    public function display(#[LiveArg] int $id): void
    {
        $this->frameConfigurationService->setMode(DisplayMode::ARTSY);
        $this->frameConfigurationService->setNextImageId($id);
        $this->frameConfigurationService->setNext(true);

        $this->wasDisplayed = true;
    }

    public function getButtonCss(): string
    {
        return sprintf('btn %s mt-auto', $this->wasDisplayed ? 'btn-secondary' : 'btn-primary');
    }

    public function isDisabled(): string
    {
        return $this->wasDisplayed ? 'disabled' : '';
    }
}
