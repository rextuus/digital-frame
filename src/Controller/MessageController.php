<?php

namespace App\Controller;

use App\Service\Displate\Message\CollectDisplateImageMessage;
use App\Service\Displate\Message\CollectSearchTagPageMessage;
use App\Service\Displate\Message\DetermineSearchTagTotalCountMessage;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class MessageController extends AbstractController
{
    #[Route('/queue/messages', name: 'queue_messages')]
    public function index(Connection $connection): Response
    {
        $rows = $connection->fetchAllAssociative('SELECT * FROM messenger_messages');

        $messages = array_map(function ($row) {
            $body = json_decode($row['body'], true);

            $type = $body['type'] ?? 'Unknown';
            $payload = $body['message'] ?? [];

            $info = [
                'id' => $row['id'],
                'queue_name' => $row['queue_name'],
                'created_at' => $row['created_at'],
                'type' => $type,
                'tagId' => null,
                'displateId' => null,
                'isLast' => null,
            ];

            switch ($type) {
                case CollectDisplateImageMessage::class:
                    $info['tagId'] = $payload['tagId'] ?? null;
                    $info['displateId'] = $payload['displateId'] ?? null;
                    $info['isLast'] = $payload['isLast'] ?? false;
                    break;

                case CollectSearchTagPageMessage::class:
                case DetermineSearchTagTotalCountMessage::class:
                    $info['tagId'] = $payload['tagId'] ?? null;
                    break;
            }

            return $info;
        }, $rows);

        return $this->render('message/index.html.twig', [
            'messages' => $messages,
        ]);
    }


    #[Route('/queue/messages/{id}', name: 'queue_message_detail')]
    public function detail(int $id, Connection $connection): Response
    {
        $message = $connection->fetchAssociative(
            'SELECT * FROM messenger_messages WHERE id = :id',
            ['id' => $id]
        );

        if (!$message) {
            throw $this->createNotFoundException("Message with ID $id not found.");
        }

        $decoded = json_decode($message['body'], true);

        return $this->render('message/show.html.twig', [
            'id' => $id,
            'queue_name' => $message['queue_name'],
            'created_at' => $message['created_at'],
            'body' => $decoded,
            'raw_body' => $message['body'],
        ]);
    }
}
