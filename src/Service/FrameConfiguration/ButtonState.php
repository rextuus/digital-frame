<?php

declare(strict_types=1);

namespace App\Service\FrameConfiguration;

class ButtonState
{
    private const DEFAULT_BUTTON_CLASSES = 'btn btn-primary';

    public const ENABLED_CLASS = 'btn-enabled';
    public const DISABLED_CLASS = 'btn-disabled';

    public function __construct(
        private readonly string $statusClass,
        private bool $isDisabled = false
    ) {
        if ($this->statusClass === self::DISABLED_CLASS) {
            $this->isDisabled = true;
        }
    }

    public function getStatusClass(): string
    {
        return $this->statusClass;
    }

    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }

    public function buttonClasses(): string
    {
        return self::DEFAULT_BUTTON_CLASSES . ' ' . $this->getStatusClass();
    }
}
