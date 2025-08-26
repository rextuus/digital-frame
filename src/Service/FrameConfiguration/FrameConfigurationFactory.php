<?php

namespace App\Service\FrameConfiguration;

use App\Entity\FrameConfiguration;
use App\Service\FrameConfiguration\Form\ConfigurationUpdateData;

class FrameConfigurationFactory
{
    public function createConfiguration(DisplayMode $mode): FrameConfiguration
    {
        $configuration = $this->getInstance();
        $configuration->setMode($mode);
        return $configuration;
    }

    public function mapData(FrameConfiguration $configuration, ConfigurationUpdateData $data): FrameConfiguration
    {
        $configuration->setMode($data->getMode());
        $configuration->setGreetingDisplayTime($data->getGreetingDisplayTime());
        $configuration->setCurrentTag($data->getCurrentTag());
        $configuration->setNext($data->isNext());
        $configuration->setShutDownTime($data->getShutDownTime());
        $configuration->setCurrentFavoriteList($data->getCurrentFavoriteList());
        $configuration->setCurrentFavoriteListIndex($data->getCurrentFavoriteListIndex());

        return $configuration;
    }

    private function getInstance(): FrameConfiguration
    {
        return new FrameConfiguration();
    }
}