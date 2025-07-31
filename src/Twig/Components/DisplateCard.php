<?php

namespace App\Twig\Components;

use App\Entity\ArtsyImage;
use App\Entity\DisplateImage;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class DisplateCard
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public DisplateImage $image;

    #[LiveProp]
    public bool $wasDisplayed = false;

    public function __construct(
        private readonly FrameConfigurationService $frameConfigurationService,
    ) {
    }

    #[LiveAction]
    public function display(#[LiveArg] int $id): void
    {
        $this->frameConfigurationService->setMode(DisplayMode::DISPLATE);
        $this->frameConfigurationService->setNextImageId($id);
        $this->frameConfigurationService->setNext(true);
        $this->frameConfigurationService->setWaitForModeSwitch(true);

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
