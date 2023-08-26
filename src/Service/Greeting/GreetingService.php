<?php

declare(strict_types=1);

namespace App\Service\Greeting;

use App\Entity\Greeting;
use App\Entity\User;
use App\Message\GreetingUpload;
use App\Repository\GreetingRepository;
use App\Service\Greeting\Form\GreetingData;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class GreetingService
{

    public function __construct(
        private GreetingRepository $repository,
        private GreetingFactory $factory
    )
    {
    }

    public function createByData(GreetingData $data): Greeting
    {
        $Greeting = $this->factory->create();
        $this->factory->mapData($Greeting, $data);

        $this->repository->save($Greeting);

        return $Greeting;
    }

    public function update(Greeting $Greeting, GreetingData $data): void
    {
        $this->factory->mapData($Greeting, $data);
        $this->repository->save($Greeting);
    }

    public function find(int $GreetingId): ?Greeting
    {
        return $this->repository->find($GreetingId);
    }

    /**
     * @param User $frame
     * @return Greeting[]
     */
    public function getNewNonDisplayedGreetings(): array
    {
       return $this->repository->findBy(['displayed' => null]);
    }
}