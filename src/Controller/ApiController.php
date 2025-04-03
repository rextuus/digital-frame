<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\FrameConfiguration\DisplayState;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(DISPLAY_SWITCH_SCRIPT_PATH)%')]
        private readonly string $scriptPath,
    ) {
    }

    #[Route('/switch/on', name: 'api_display_on', methods: ['GET'])]
    public function turnDisplayOn(): JsonResponse
    {
        return $this->executeCommand(DisplayState::ON);
    }

    #[Route('/switch/off', name: 'api_display_off', methods: ['GET'])]
    public function turnDisplayOff(): JsonResponse
    {
        return $this->executeCommand(DisplayState::OFF);
    }

    private function executeCommand(DisplayState $displayState): JsonResponse
    {
        if (!file_exists($this->scriptPath) || !is_readable($this->scriptPath) || !is_executable($this->scriptPath)) {
            return new JsonResponse([
                'error' => 'Python script not found or is not readable.',
            ], 500);
        }

        // Escaping shell arguments for safety
        $safeCommand = escapeshellarg($displayState->value);

        // Execute the Python script
        $output = [];
        $returnCode = null;

        exec("python3 {$this->scriptPath} $safeCommand", $output, $returnCode);

        if ($returnCode !== 0) {
            return new JsonResponse([
                'error' => 'Command execution failed.',
            ], 500);
        }

        return new JsonResponse([
            'state' => sprintf('%s', $displayState->value)
        ], 200);
    }
}
