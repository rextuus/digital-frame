<?php

namespace App\Service\FrameConfiguration;

use App\Entity\FrameConfiguration;
use App\Repository\FrameConfigurationRepository;
use App\Service\FrameConfiguration\Form\ConfigurationUpdateData;

class FrameConfigurationService
{

    public function __construct(private FrameConfigurationRepository $repository, private FrameConfigurationFactory $factory)
    {
    }

    public function createConfiguration(int $mode): FrameConfiguration
    {
        $configuration = $this->factory->createConfiguration($mode);
        $configuration->setNext(false);
        $configuration->setCurrentTag('random');
        $configuration->setGreetingDisplayTime(FrameConfiguration::DEFAULT_DISPLAY_TIME);
        $configuration->setShutDownTime(FrameConfiguration::DEFAULT_DOWN_TIME);
        $this->repository->save($configuration, true);
        return $configuration;
    }

    public function getDefaultUpdateData(): ConfigurationUpdateData
    {
        $configuration = $this->repository->find(1);

        return (new ConfigurationUpdateData())->initFrom($configuration);
    }
    public function updateConfiguration(?int $mode, bool $isNext = false, string $defaultGreetingTime = FrameConfiguration::DEFAULT_DISPLAY_TIME){
        $configuration = $this->getConfiguration();

        if (is_null($mode)){
            $mode = $configuration->getMode();
        }
        $configuration->setMode($mode);
        $configuration->setNext($isNext);

        $this->repository->save($configuration, true);
    }

    public function update(ConfigurationUpdateData $data, FrameConfiguration $configuration = null): FrameConfiguration
    {
        if (is_null($configuration)){
            $configuration = $this->getConfiguration();
        }

        $this->factory->mapData($configuration, $data);
        $this->repository->save($configuration, true);

        return $configuration;
    }

    public function getMode(): ?int
    {
        $configuration = $this->getConfiguration();

        return $configuration->getMode();
    }

    public function isNext(): bool
    {
        $configuration = $this->getConfiguration();

        return $configuration->isNext();
    }

    public function setCurrentTag(string $tag): void
    {
        $configuration = $this->getConfiguration();

        $configuration->setCurrentTag($tag);
        $this->repository->save($configuration, true);
    }

    public function getCurrentTag(): string
    {
        $configuration = $this->getConfiguration();
        return $configuration->getCurrentTag();
    }

    public function getConfiguration(): FrameConfiguration
    {
        $configuration = $this->repository->find(1);
        if(is_null($configuration)){
            $configuration = $this->createConfiguration(1);
        }
        return $configuration;
    }
}
