<?php

namespace App\Entity;

use App\Repository\ScheduleConfigurationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleConfigurationRepository::class)]
class ScheduleConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $identifier = null;

    /**
     * @var Collection<int, ScheduleSlot>
     */
    #[ORM\OneToMany(mappedBy: 'scheduleConfiguration', targetEntity: ScheduleSlot::class, orphanRemoval: true)]
    private Collection $slots;

    public function __construct()
    {
        $this->slots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * @return Collection<int, ScheduleSlot>
     */
    public function getSlots(): Collection
    {
        return $this->slots;
    }

    public function addSlot(ScheduleSlot $slot): static
    {
        if (!$this->slots->contains($slot)) {
            $this->slots->add($slot);
            $slot->setScheduleConfiguration($this);
        }

        return $this;
    }

    public function removeSlot(ScheduleSlot $slot): static
    {
        if ($this->slots->removeElement($slot)) {
            // set the owning side to null (unless already changed)
            if ($slot->getScheduleConfiguration() === $this) {
                $slot->setScheduleConfiguration(null);
            }
        }

        return $this;
    }
}
