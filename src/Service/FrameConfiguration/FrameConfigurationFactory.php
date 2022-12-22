<?php

namespace App\Service\FrameConfiguration;

use App\Entity\FrameConfiguration;

class FrameConfigurationFactory
{
    public function createConfiguration(int $mode): FrameConfiguration
    {
        $configuration = $this->getInstance();
        $configuration->setMode($mode);
        $configuration->setCurrentTag('random');
        return $configuration;
    }

    private function getInstance(): FrameConfiguration
    {
        return new FrameConfiguration();
    }
}