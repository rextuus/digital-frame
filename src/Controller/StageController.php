<?php

namespace App\Controller;

use App\Service\Scheduling\Message\RunScheduleMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stage')]
class StageController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/', name: 'app_stage_show')]
    public function stage(): Response
    {
        $hasScheduledMessage = $this->hasPendingMessages();

        if (!$hasScheduledMessage) {
            $this->messageBus->dispatch(new RunScheduleMessage());
        }

        return $this->render('stage/show.html.twig');
    }

    private function hasPendingMessages(): bool
    {
        // Check Doctrine transport `messenger_messages` table for pending messages
        $connection = $this->entityManager->getConnection();
        $sql = 'SELECT COUNT(*) as count FROM messenger_messages WHERE delivered_at IS NULL';
        $result = $connection->fetchOne($sql);

        return (int) $result > 0;
    }
}
