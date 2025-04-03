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
        private readonly string $switchStateScriptPath,
        #[Autowire('%env(ROTATE_DISPLAY_SCRIPT_PATH)%')]
        private readonly string $rotateDisplayScriptPath,
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
        if (!file_exists($this->switchStateScriptPath) || !is_readable($this->switchStateScriptPath)) {
            return new JsonResponse(['error' => 'Python script not found or is not readable.'], 500);
        }

        $safeCommand = escapeshellarg($displayState->value);
        $output = [];
        $returnCode = null;

        // Execute Python script
        exec("/usr/bin/python3 {$this->switchStateScriptPath} $safeCommand 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            return new JsonResponse([
                'error' => 'Command execution failed.',
                'output' => $output,
                'return_code' => $returnCode,
                'command' => "/usr/bin/python3 {$this->switchStateScriptPath} $safeCommand",
            ], 500);
        }

        // Execute the shell script after turning the screen on
        if ($displayState->value === 'on') {
            sleep(5);
            if (file_exists($this->rotateDisplayScriptPath) && is_executable($this->rotateDisplayScriptPath)) {
                exec("bash $this->rotateDisplayScriptPath 2>&1", $shellOutput, $shellReturnCode);

                if ($shellReturnCode !== 0) {
                    return new JsonResponse([
                        'error' => 'Shell script execution failed.',
                        'shell_script_output' => $shellOutput,
                        'shell_return_code' => $shellReturnCode,
                        'shell_command' => "bash $this->rotateDisplayScriptPath",
                    ], 500);
                }
            } else {
                return new JsonResponse(['error' => 'Shell script not found or is not executable.'], 500);
            }
        }

        return new JsonResponse(['state' => $displayState->value, 'output' => $output], 200);
    }

    #[Route('/status', name: 'api_display_status', methods: ['GET'])]
    public function getDisplayStatus(): JsonResponse
    {
        // Logic to determine the current state of the display
        $displayState = $this->retrieveCurrentState();

        if (null === $displayState) {
            return new JsonResponse(['error' => 'Unable to determine display state.'], 500);
        }

        return new JsonResponse(['state' => $displayState], 200);
    }

    private function retrieveCurrentState(): ?string
    {
        // Use the script or some other logic to fetch the current state.
        // For example, a Python script or direct OS/system call could be used.

        if (!file_exists($this->switchStateScriptPath) || !is_executable($this->switchStateScriptPath)) {
            return null; // Unable to check state
        }

        // Assume the script returns "on" or "off".
        $output = [];
        $returnCode = null;
        exec("/usr/bin/python3 {$this->switchStateScriptPath} status 2>&1", $output, $returnCode); // Assume "status" argument checks the state

        if ($returnCode !== 0) {
            return null; // Failed to retrieve the state
        }

        return strtolower(trim($output[0] ?? '')); // Expect "on" or "off" as output
    }

}
