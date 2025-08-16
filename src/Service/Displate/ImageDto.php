<?php

declare(strict_types=1);

namespace App\Service\Displate;


class ImageDto
{
    public function __construct(
        private ?string $url = null,
        private ?string $name = null,
        private ?int $width = null,
        private ?int $height = null,
        private ?string $link = null,
        private ?int $displateId = null,
        private ?int $totalPagesForSearchTag = null,
        private ?string $previewUrl = null
    ) {
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): ImageDto
    {
        $this->url = $url;
        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): ImageDto
    {
        $this->name = $name;
        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): ImageDto
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): ImageDto
    {
        $this->height = $height;
        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): ImageDto
    {
        $this->link = $link;
        return $this;
    }

    public function getDisplateId(): ?int
    {
        return $this->displateId;
    }

    public function setDisplateId(?int $displateId): ImageDto
    {
        $this->displateId = $displateId;
        return $this;
    }

    public function getTotalPagesForSearchTag(): ?int
    {
        return $this->totalPagesForSearchTag;
    }

    public function setTotalPagesForSearchTag(?int $totalPagesForSearchTag): ImageDto
    {
        $this->totalPagesForSearchTag = $totalPagesForSearchTag;
        return $this;
    }

    public function getPreviewUrl(): ?string
    {
        return $this->previewUrl;
    }

    public function setPreviewUrl(?string $previewUrl): self
    {
        $this->previewUrl = $previewUrl;
        return $this;
    }
}
