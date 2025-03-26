<?php

declare(strict_types=1);

namespace App\Service\Nasa;

use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class NasaService
{
    private const NASA_APOD_URL = 'https://api.nasa.gov/planetary/apod';
    private Client $httpClient;

    public function __construct(
        #[Autowire(env: 'NASA_API_KEY')]
        private readonly string $apiKey,
    ) {
        $this->httpClient = new Client();
    }

    public function getImageOfTheDay(): NasaImageOfTheDay
    {
        $url = self::NASA_APOD_URL . '?api_key=' . $this->apiKey;

        $imageOfTheDay = new NasaImageOfTheDay();
        try {
            $response = $this->httpClient->request('GET', $url, [
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $imageOfTheDay->setUrl($data['hdurl']);
            $imageOfTheDay->setExplanation($data['explanation']);
            $imageOfTheDay->setDate(new DateTime($data['date']));
            $imageOfTheDay->setTitle($data['title']);
        } catch (GuzzleException $e) {
            dd($e);
        }

        return $imageOfTheDay;
    }
}
