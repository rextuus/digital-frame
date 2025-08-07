<?php

namespace App\Controller;

use App\Entity\BackgroundConfiguration;
use App\Form\ScheduleFrameData;
use App\Form\ScheduleFrameType;
use App\Service\Displate\DisplateImageService;
use App\Service\Favorite\FavoriteService;
use App\Service\Favorite\ModeToFavoriteConvertProvider;
use App\Service\FrameConfiguration\BackgroundStyle;
use App\Service\FrameConfiguration\DisplayMode;
use App\Service\FrameConfiguration\Form\ConfigurationData;
use App\Service\FrameConfiguration\Form\ConfigurationType;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\FrameConfiguration\ImageStyle;
use App\Service\Scheduling\ScheduleAction;
use App\Service\Scheduling\ScheduleConfigurationService;
use App\Service\Unsplash\UnsplashImageService;
use ColorThief\ColorThief;
use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConfigurationController extends AbstractController
{
    public function __construct(
        private readonly ModeToFavoriteConvertProvider $modeToFavoriteConvertProvider,
        private readonly FrameConfigurationService $configurationService,
        private readonly UnsplashImageService $unsplashImageService
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_configuration_landing')]
    public function view(Request $request, FavoriteService $favoriteService): Response
    {
        $currentMode = $this->configurationService->getMode();
        $currentTag = $this->configurationService->getCurrentTag();

        $configurationData = new ConfigurationData();
        $configurationData->setMode(1);
        $configurationData->setNewTag(null);
        $configurationData->setTag($currentTag);
        $backgroundStyle = $this->configurationService->getBackgroundConfigurationForCurrentMode();
        if ($backgroundStyle->getColor() !== null) {
            $configurationData->setColor($backgroundStyle->getColor());
        }
        if ($backgroundStyle->getCustomHeight() !== null) {
            $configurationData->setHeight($backgroundStyle->getCustomHeight());
        }
        if ($backgroundStyle->getCustomMargin() !== null) {
            $configurationData->setMargin($backgroundStyle->getCustomMargin());
        }
        $form = $this->createForm(ConfigurationType::class, $configurationData);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $storeFavorite = $form->get('store')->isClicked();

            $newMode = $currentMode;
            $next = $form->get('next')->isClicked();

            // detect new mode
            if (!$next) {
                $newMode = $this->getNewMode($form);
                if ($newMode === null) {
                    $newMode = $currentMode;
                }
            }

            // handle setting toggles
            $this->handleToggleButtons($form);

            // when new mode is given we mark this in config and can wait until it will be changed from stage
            $waitUntilIsSwitchedViaStageController = $currentMode !== $newMode || $next;
            if ($waitUntilIsSwitchedViaStageController) {
                $this->configurationService->setWaitForModeSwitch(true);
            }

            /** @var ConfigurationData $data */
            $data = $form->getData();

            $updateData = $this->configurationService->getDefaultUpdateData();
            $updateData->setMode($newMode);
            $updateData->setNext($next);
            $this->configurationService->update($updateData);

            // update Tag if new one was added
            $currentTag = $data->getTag();
            if ($data->getNewTag()) {
                $newTag = $this->unsplashImageService->createNewTag($data->getNewTag());
                $currentTag = $newTag;
            }

            $this->configurationService->setCurrentTag($currentTag);

            if ($storeFavorite) {
                $converter = $this->modeToFavoriteConvertProvider->getFittingConverter();
                $favoriteService->storeFavorite($converter->convertToFavoriteEntity());
            }

            // change backgroundColor for current mode
            $this->handleBackgroundButtons($form, $data, $backgroundStyle);

            // wait for switch
            if ($waitUntilIsSwitchedViaStageController) {
                $counter = 0;
                $waitForSwitch = true;
                while ($waitForSwitch) {
                    sleep(1);

                    $waitForSwitch = $this->configurationService->isWaitingForModeSwitch();
                    if (!$waitForSwitch) {
                        sleep(1);
                    }

                    $counter++;
                    if ($counter > 30) {
                        throw new Exception('Timeout waiting for mode switch');
                    }
                }
            }

            return $this->redirectToRoute('app_configuration_landing');
        }

        $converter = $this->modeToFavoriteConvertProvider->getFittingConverter();

        $buttonMap = $this->configurationService->getActiveButtonMap();

        $nextButtonDisabled = $currentMode !== DisplayMode::UNSPLASH;

        return $this->render('configuration/landing.html.twig', [
            'form' => $form->createView(),
            'buttonMap' => $buttonMap,
            'lastImageDto' => $converter->getLastImageDto(),
            'backgroundColor' => $backgroundStyle,
            'nextButtonDisabled' => $nextButtonDisabled
        ]);
    }

    private function getNewMode(FormInterface $form): ?DisplayMode
    {
        $isSpotify = $form->get('spotify')->isClicked();
        $isImage = $form->get('image')->isClicked();
        $isArtsy = $form->get('artsy')->isClicked();
        $isGreeting = $form->get('greeting')->isClicked();
        $isNasa = $form->get('nasa')->isClicked();
        $isDisplate = $form->get('displate')->isClicked();

        $newMode = null;
        if ($isArtsy) {
            $newMode = DisplayMode::ARTSY;
        } elseif ($isGreeting) {
            $newMode = DisplayMode::GREETING;
        } elseif ($isSpotify) {
            $newMode = DisplayMode::SPOTIFY;
        } elseif ($isImage) {
            $newMode = DisplayMode::UNSPLASH;
        } elseif ($isNasa) {
            $newMode = DisplayMode::NASA;
        } elseif ($isDisplate) {
            $newMode = DisplayMode::DISPLATE;
        }

        return $newMode;
    }

    private function handleToggleButtons(FormInterface $form): void
    {
        $isGreetingInterruption = $form->get('greetingInterruption')->isClicked();
        $isSpotifyInterruption = $form->get('spotifyInterruption')->isClicked();

        if ($isGreetingInterruption) {
            $this->configurationService->toggleShouldGreetingInterrupt();
        }

        if ($isSpotifyInterruption) {
            $this->configurationService->toggleShouldSpotifyInterrupt();
        }
    }

    private function handleBackgroundButtons(
        FormInterface $form,
        ConfigurationData $data,
        BackgroundConfiguration $backgroundStyle
    ): void {
        $changeColor = $form->get('changeColor')->isClicked();
        $blur = $form->get('blur')->isClicked();
        $clear = $form->get('clear')->isClicked();
        $maximize = $form->get('maximize')->isClicked();
        $customHeight = $form->get('customHeight')->isClicked();

        if ($changeColor) {
            $this->configurationService->setBackgroundColorForCurrentMode($data->getColor());
        }
        if ($blur) {
            $this->configurationService->setBackgroundStyleForCurrentMode(BackgroundStyle::BLUR);
        }
        if ($clear) {
            $this->configurationService->setBackgroundStyleForCurrentMode(BackgroundStyle::CLEAR);
            if ($backgroundStyle->getImageStyle() === ImageStyle::ORIGINAL) {
                $this->configurationService->toggleImageStyleForCurrentMode();
            }
        }
        if ($maximize) {
            $this->configurationService->toggleImageStyleForCurrentMode();
        }
        if ($customHeight){
            $customHeight = $data->getHeight();
            if ($customHeight === null) {
                $customHeight = 1900;
            }
            $customMargin = $data->getMargin();
            if ($customMargin=== null) {
                $customMargin = 0;
            }
            $this->configurationService->setImageStyleForCurrentMode(ImageStyle::CUSTOM_HEIGHT, $customHeight, $customMargin);
        }
    }

    #[Route('/configuration/background', name: 'app_configuration_background')]
    public function calculateBackgroundColor(Request $request): Response
    {
        $backgroundColor = [0 => 0, 1 => 0, 2 => 0];

        $imageUrl = $request->query->get('url');
        if ($imageUrl) {
            try {
                $backgroundColor = ColorThief::getColor($imageUrl, 6);
            } catch (Exception $e) {
                $backgroundColor[3] = $e->getTraceAsString();
            }
        }
        return new JsonResponse($backgroundColor);
    }

    #[Route('/scheduler', name: 'app_configuration_scheduler')]
    public function scheduler(Request $request, ScheduleConfigurationService $scheduleConfigurationService): Response
    {
        $configuration = $scheduleConfigurationService->getConfiguration();

//        $start = new DateTime('00:00:00');
//        $finish = new DateTime('05:00:00');
//        $scheduleConfigurationService->addScheduleSlot($start, $finish, ScheduleAction::SHOW_RANDOM_FAVORITE_FROM_LIST, 5);
//        $scheduleConfigurationService->resetSchedule();

        return $this->render('configuration/scheduler.html.twig', [
        ]);
    }

    #[Route('/scheduler/edit/{identifier}', name: 'app_configuration_scheduler_edit')]
    public function edit(string $identifier, ScheduleConfigurationService $scheduleConfigurationService, Request $request): Response
    {
        // Retrieve existing schedule slot using the identifier
        $timeSlot = $scheduleConfigurationService->findSlotByIdentifier($identifier);

        if (!$timeSlot) {
            throw $this->createNotFoundException(sprintf('No schedule slot found with identifier "%s".', $identifier));
        }

        // Create the ScheduleFrameData DTO and populate it with the slot's details
        $dto = new ScheduleFrameData();
        $dto->setIdentifier($timeSlot->getIdentifier())
            ->setFromHour($timeSlot->getFromHour())
            ->setFromMinute($timeSlot->getFromMinute())
            ->setToHour($timeSlot->getToHour())
            ->setInterval($timeSlot->getInterval())
            ->setToMinute($timeSlot->getToMinute());

        $info = explode('|', $timeSlot->getIdentifier());
        $action = ScheduleAction::tryFrom($info[0]);
        if ($action === null) {
            $action = ScheduleAction::DEFAULT;
        }

        $dto->setAction($action); // Assuming `getAction` exists

        // Create and handle the form
        $form = $this->createForm(ScheduleFrameType::class, $dto, [
            'identifiers' => [$identifier],
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the schedule slot using the submitted data
            /** @var ScheduleFrameData $data */
            $data = $form->getData();

            // Assuming you have a method to update the slot
            $start = new DateTime(
                sprintf('%s:%s:00', $data->getFromHour(), $data->getFromMinute())
            );
            $finish = new DateTime(
                sprintf('%s:%s:00', $data->getToHour(), $data->getToMinute())
            );

            $scheduleConfigurationService->addScheduleSlot($start, $finish, $data->getAction(), $data->getInterval());

            // Redirect to a success page or another part of the configuration
            return $this->redirectToRoute('app_configuration_scheduler');
        }

        return $this->render('configuration/scheduler_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
