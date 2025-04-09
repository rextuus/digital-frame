<?php

namespace App\Entity;

use App\Repository\BackgroundConfigurationRepository;
use App\Service\FrameConfiguration\BackgroundStyle;
use App\Service\FrameConfiguration\DisplayMode;
use Doctrine\ORM\Mapping as ORM;
use \App\Service\FrameConfiguration\ImageStyle;

#[ORM\Entity(repositoryClass: BackgroundConfigurationRepository::class)]
class BackgroundConfiguration
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'backgroundConfigurations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?FrameConfiguration $configuration = null;

    #[ORM\Column(nullable: false, enumType: BackgroundStyle::class, options: ['default' => BackgroundStyle::COLOR])]
    private BackgroundStyle $style;

    #[ORM\Column(enumType: DisplayMode::class)]
    private ?DisplayMode $mode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(nullable: false, enumType: ImageStyle::class, options: ['default' => ImageStyle::ORIGINAL])]
    private ImageStyle $imageStyle;

    #[ORM\Column(nullable: true)]
    private ?int $customHeight = null;

    #[ORM\Column(nullable: true)]
    private ?int $customMargin = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfiguration(): ?FrameConfiguration
    {
        return $this->configuration;
    }

    public function setConfiguration(?FrameConfiguration $configuration): static
    {
        $this->configuration = $configuration;

        return $this;
    }

    public function getStyle(): BackgroundStyle
    {
        return $this->style;
    }

    public function setStyle(BackgroundStyle $style): static
    {
        $this->style = $style;

        return $this;
    }

    public function getMode(): ?DisplayMode
    {
        return $this->mode;
    }

    public function setMode(DisplayMode $mode): static
    {
        $this->mode = $mode;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getImageStyle(): ImageStyle
    {
        return $this->imageStyle;
    }

    public function setImageStyle(ImageStyle $imageStyle): static
    {
        $this->imageStyle = $imageStyle;

        return $this;
    }

    public function getCustomHeight(): ?int
    {
        return $this->customHeight;
    }

    public function setCustomHeight(?int $customHeight): static
    {
        $this->customHeight = $customHeight;

        return $this;
    }

    public function getCustomMargin(): ?int
    {
        return $this->customMargin;
    }

    public function setCustomMargin(?int $customMargin): static
    {
        $this->customMargin = $customMargin;

        return $this;
    }
}
