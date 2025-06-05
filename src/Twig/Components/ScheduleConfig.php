<?php

namespace App\Twig\Components;

use App\Form\ScheduleFrameData;
use App\Form\ScheduleFrameType;
use App\Repository\ScheduleSlotRepository;
use App\Service\Scheduling\ScheduleAction;
use App\Service\Scheduling\ScheduleConfigurationService;
use App\Service\Scheduling\TimeSlotFrameDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class ScheduleConfig
{
    use DefaultActionTrait;

    public function __construct(
        private readonly ScheduleSlotRepository $scheduleSlotRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /**
     * @return array<TimeSlotFrameDto>
     */
    public function getSlots(): array
    {
        return $this->scheduleSlotRepository->getSlotsByIdentifier();
    }


    public function getBackgroundColorForIdentifier(string $identifier): string
    {
        if ($identifier === ScheduleConfigurationService::DEFAULT_FRAME_IDENTIFIER) {
            return 'bg-primary';
        }

        return 'bg-secondary';
    }
}