<?php

declare(strict_types=1);

namespace App\Service\FrameConfiguration;

class ButtonStateCollection
{
    /**
     * @var array<ButtonState>
     */
    private array $states = [];

    public function addButton(string $name, ButtonState $state): void
    {
        $this->states[$name] = $state;
    }

    public function getButton(string $name): ButtonState
    {
        return $this->states[$name];
    }

    public function getButtonClasses(string $name): string
    {
        return $this->states[$name]->buttonClasses();
    }

    public function getButtonState(string $name): string
    {
        return $this->states[$name]->isDisabled() ? 'false' : 'true';
    }
}
