<?php

declare(strict_types=1);

namespace App\Service\Greeting;

use App\Entity\Greeting;
use App\Service\Greeting\Form\GreetingCreateData;
use App\Service\Greeting\Form\GreetingData;
use DateTime;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GreetingFactory
{
    public function __construct() { }

    public function create(): Greeting
    {
        $greeting = $this->getNewInstance();

        return $greeting;
    }

    public function mapData(Greeting $greeting, GreetingData $data): void
    {
        if ($data instanceof GreetingCreateData) {
            $greeting->setCreated(new DateTime());
        }

        $greeting->setName($data->getName());
        $greeting->setUploaded($data->getUploaded());
        $greeting->setDelivered($data->getDelivered());
        $greeting->setDisplayed($data->getDisplayed());
        $greeting->setCdnUrl($data->getCdnUrl());
        $greeting->setRemoteId($data->getRemoteId());
    }

    private function getNewInstance(): Greeting
    {
        return new Greeting();
    }
}
