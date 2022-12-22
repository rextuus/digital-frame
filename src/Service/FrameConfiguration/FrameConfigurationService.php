<?php

namespace App\Service\FrameConfiguration;

use App\Entity\FrameConfiguration;
use App\Repository\FrameConfigurationRepository;

class FrameConfigurationService
{

    public function __construct(private FrameConfigurationRepository $repository, private FrameConfigurationFactory $factory)
    {
    }

    public function createConfiguration(int $mode): FrameConfiguration
    {
        $configuration = $this->factory->createConfiguration($mode);
        $configuration->setNext(false);
        $this->repository->save($configuration, true);
        return $configuration;
    }

    public function updateConfiguration(?int $mode, bool $isNext = false){
        $configuration = $this->repository->find(1);
        if (is_null($mode)){
            $mode = $configuration->getMode();
        }
        $configuration->setMode($mode);
        $configuration->setNext($isNext);

        $this->repository->save($configuration, true);
    }

    public function getMode(): ?int
    {
        $configuration = $this->repository->find(1);
        if (is_null($configuration)){
            $configuration = $this->createConfiguration(1);
        }
        return $configuration->getMode();
    }

    public function isNext(): bool
    {
        $configuration = $this->repository->find(1);
        return $configuration->isNext();
    }

    public function setCurrentTag(string $tag): void
    {
        $configuration = $this->repository->find(1);
        $configuration->setCurrentTag($tag);
        $this->repository->save($configuration, true);
    }

    public function getCurrentTag(): string
    {
        $configuration = $this->repository->find(1);
        return $configuration->getCurrentTag();
    }
}
