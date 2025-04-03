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
        if (!file_exists($this->scriptPath) || !is_readable($this->scriptPath)) {
            return new JsonResponse(['error' => 'Python script not found or is not readable.'], 500);
        }

        $safeCommand = escapeshellarg($displayState->value);
        $output = [];
        $returnCode = null;

        // Execute Python script
        exec("/usr/bin/python3 {$this->scriptPath} $safeCommand 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            return new JsonResponse([
                'error' => 'Command execution failed.',
                'output' => $output,
                'return_code' => $returnCode,
                'command' => "/usr/bin/python3 {$this->scriptPath} $safeCommand",
            ], 500);
        }

        // Execute the shell script after turning the screen on
        if ($displayState->value === 'on') {
            $shellScriptPath = '/path/to/your/shell_script.sh';
            if (file_exists($shellScriptPath) && is_executable($shellScriptPath)) {
                exec("bash $shellScriptPath 2>&1", $shellOutput, $shellReturnCode);

                if ($shellReturnCode !== 0) {
                    return new JsonResponse([
                        'error' => 'Shell script execution failed.',
                        'shell_script_output' => $shellOutput,
                        'shell_return_code' => $shellReturnCode,
                        'shell_command' => "bash $shellScriptPath",
                    ], 500);
                }
            } else {
                return new JsonResponse(['error' => 'Shell script not found or is not executable.'], 500);
            }
        }

        return new JsonResponse(['state' => $displayState->value, 'output' => $output], 200);
    }
}
