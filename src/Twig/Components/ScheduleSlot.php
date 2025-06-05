<?php

namespace App\Twig\Components;

use App\Service\Scheduling\TimeSlotFrameDto;
use App\Service\Scheduling\ScheduleAction;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ScheduleSlot
{
    use DefaultActionTrait;

    public ?TimeSlotFrameDto $slot = null;

    /**
     * Get the slot action details: Enum case, background color, and font-awesome icon.
     */
    public function getSlotActionDetails(): array
    {
        if (!$this->slot) {
            return [
                'action' => ScheduleAction::DEFAULT,
                'bgColor' => 'bg-secondary',
                'icon' => 'fas fa-question-circle'
            ];
        }

        // Extract action identifier from the slot's identifier
        $actionIdentifier = explode('|', $this->slot->identifier);

        // Attempt to match the identifier to a ScheduleAction enum case
        $action = ScheduleAction::tryFrom($actionIdentifier[0]) ?? ScheduleAction::DEFAULT;

        // Define colors and font-awesome icons for each case
        return match ($action) {
            ScheduleAction::DEFAULT => [
                'action' => $action,
                'bgColor' => 'bg-secondary',
                'icon' => 'fas fa-question-circle',
                'identifier' => $actionIdentifier[0]
            ],
            ScheduleAction::SHOW_RANDOM_DISPLATE_FROM_SEARCH_TAG => [
                'action' => $action,
                'bgColor' => 'bg-primary',
                'icon' => 'fas fa-search',
                'identifier' => $actionIdentifier[0]
            ],
            ScheduleAction::SHOW_RANDOM_FAVORITE_FROM_LIST => [
                'action' => $action,
                'bgColor' => 'bg-success',
                'icon' => 'fas fa-heart',
                'identifier' => $actionIdentifier[0]
            ],
        };
    }
}