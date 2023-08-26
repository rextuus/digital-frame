<?php

declare(strict_types=1);

namespace App\Service\Greeting\Form;

use App\Entity\Greeting;
use App\Entity\User;
use DateTimeInterface;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GreetingData
{
    private string $filePath;
    private ?string $cdnUrl = null;
    private ?DateTimeInterface $displayed = null;
    private ?DateTimeInterface $delivered = null;
    private ?DateTimeInterface $uploaded = null;
    private string $name;
    private int $remoteId;

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): GreetingData
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function getCdnUrl(): ?string
    {
        return $this->cdnUrl;
    }

    public function setCdnUrl(?string $cdnUrl): GreetingData
    {
        $this->cdnUrl = $cdnUrl;
        return $this;
    }

    public function getDisplayed(): ?DateTimeInterface
    {
        return $this->displayed;
    }

    public function setDisplayed(?DateTimeInterface $displayed): GreetingData
    {
        $this->displayed = $displayed;
        return $this;
    }

    public function getDelivered(): ?DateTimeInterface
    {
        return $this->delivered;
    }

    public function setDelivered(?DateTimeInterface $delivered): GreetingData
    {
        $this->delivered = $delivered;
        return $this;
    }

    public function getUploaded(): ?DateTimeInterface
    {
        return $this->uploaded;
    }

    public function setUploaded(?DateTimeInterface $uploaded): GreetingData
    {
        $this->uploaded = $uploaded;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): GreetingData
    {
        $this->name = $name;
        return $this;
    }

    public function getRemoteId(): int
    {
        return $this->remoteId;
    }

    public function setRemoteId(int $remoteId): GreetingData
    {
        $this->remoteId = $remoteId;
        return $this;
    }

    public function initFrom(Greeting $Greeting): GreetingData
    {
        $this->setUploaded($Greeting->getUploaded());
        $this->setDelivered($Greeting->getDelivered());
        $this->setDisplayed($Greeting->getDisplayed());
        $this->setCdnUrl($Greeting->getCdnUrl());
        $this->setRemoteId($Greeting->getRemoteId());
        $this->setName($Greeting->getName());
        return $this;
    }
}