<?php

declare(strict_types=1);

namespace App\Service\Nasa;

class NasaImageOfTheDay
{
    private string $url;

    private string $explanation;

    private string $title;

    private \DateTime $date;

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): NasaImageOfTheDay
    {
        $this->url = $url;
        return $this;
    }

    public function getExplanation(): string
    {
        return $this->explanation;
    }

    public function setExplanation(string $explanation): NasaImageOfTheDay
    {
        $this->explanation = $explanation;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): NasaImageOfTheDay
    {
        $this->title = $title;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): NasaImageOfTheDay
    {
        $this->date = $date;
        return $this;
    }
}
