<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\Displate\DisplateImageService;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\DisplayState;
use App\Service\FrameConfiguration\FrameConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api')]
class ApiController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(DISPLAY_SWITCH_SCRIPT_PATH)%')]
        private readonly string $switchStateScriptPath,
        #[Autowire('%env(ROTATE_DISPLAY_SCRIPT_PATH)%')]
        private readonly string $rotateDisplayScriptPath,
        private readonly FrameConfigurationService $configurationService,
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

    #[Route('/switch', name: 'api_display_toggle', methods: ['POST'])]
    public function toggleDisplay(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['state']) || !in_array($data['state'], [DisplayState::ON->value, DisplayState::OFF->value], true)) {
            return new JsonResponse(['error' => 'Invalid display state. Allowed values are "on" or "off".'], 400);
        }

        $displayState = DisplayState::tryFrom($data['state']);
        if ($displayState === null) {
            return new JsonResponse(['error' => 'Invalid display state. Allowed values are "on" or "off".'], 400);
        }

        // if already on we dont need to make it on
        if ($this->configurationService->getDisplayState() === DisplayState::ON && $displayState === DisplayState::ON) {
            return new JsonResponse(['state' => $displayState->value, 'output' => ''], 200);
        }

        return $this->executeCommand($displayState);
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
        if ($displayState === DisplayState::ON) {
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

        $this->configurationService->setDisplayState($displayState);

        return new JsonResponse(['state' => $displayState->value, 'output' => $output], 200);
    }

    #[Route('/status', name: 'api_display_status', methods: ['GET'])]
    public function getDisplayStatus(): JsonResponse
    {
        $displayState = $this->configurationService->getDisplayState();

        return new JsonResponse(['state' => $displayState->value], 200);
    }

    #[Route('/next', name: 'api_display_next', methods: ['GET'])]
    public function next(): JsonResponse
    {
        $this->skipImage();

        return new JsonResponse(['state' => 'skipped'], 200);
    }

    #[Route('/block', name: 'api_display_block', methods: ['GET'])]
    public function bock(DisplateImageService $displateImageService): JsonResponse
    {
        $currentMode = $this->configurationService->getMode();

        if ($currentMode === DisplayMode::DISPLATE){
            $displateImageService->blockCurrentlyDisplayedImage();
        }

        $this->skipImage();

        return new JsonResponse(['state' => 'skipped'], 200);
    }

    private function skipImage(): void
    {
        $this->configurationService->setNext(true);
    }
}
