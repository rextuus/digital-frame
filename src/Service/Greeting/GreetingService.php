<?php

declare(strict_types=1);

namespace App\Service\Greeting;

use App\Entity\Greeting;
use App\Repository\GreetingRepository;
use App\Service\Greeting\Form\GreetingData;

class GreetingService
{
    public function __construct(
        private readonly GreetingRepository $repository,
        private readonly GreetingFactory $factory
    ) {
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
     * @return array<Greeting>
     */
    public function getNewNonDisplayedGreetings(): array
    {
       return $this->repository->findBy(['displayed' => null], ['displayed' => 'ASC']);
    }

    /**
     * @return array<Greeting>
     */
    public function getDisplayedGreetingsNeedingSync(): array
    {
        return $this->repository->getDisplayedGreetingsNeedingSync();
    }
}