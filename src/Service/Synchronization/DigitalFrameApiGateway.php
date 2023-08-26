<?php

declare(strict_types=1);

namespace App\Service\Synchronization;

use App\Service\Greeting\Form\GreetingCreateData;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2023 DocCheck Community GmbH
 */
class DigitalFrameApiGateway
{
    private const BASE_URL = 'https://digital-frame.wh-company.de';
//    private const BASE_URL = 'localhost:8001';
    private Client $httpClient;

    public function __construct(private string $digitalFrameAccount) {
        $this->httpClient = new Client();
    }

    /**
     * @return GreetingCreateData[]
     */
    public function getGreetings(): array
    {
        $url = self::BASE_URL . '/frame/'.$this->digitalFrameAccount.'/synchronize/images';

        $queryParams = [];

        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => $queryParams,
            ]);

            $greetingDatas = [];
            $body = json_decode($response->getBody()->getContents(), true);
            foreach ($body['images'] as $image){
                $data = new GreetingCreateData();
                $data->setUploaded(new DateTime($image['uploaded']));
                $data->setCdnUrl($image['cdnUrl']);
                $data->setName($image['name']);
                $data->setRemoteId($image['id']);

                $greetingDatas[] = $data;
            }

            return $greetingDatas;
        } catch (Exception $e) {
            // Handle errors appropriately
            throw $e;
        }
    }

    /**
     * @param int[] $ids
     * @throws GuzzleException
     */
    public function markGreetingsAsDelivered(array $ids): void
    {
        $url = self::BASE_URL . '/frame/images/mark/delivered';

        try {
            $response = $this->httpClient->request('POST', $url, [
                'form_params' => ['ids' => $ids],
            ]);
            $body = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            // Handle errors appropriately
            throw $e;
        }
    }

    /**
     * @param int[] $ids
     * @throws GuzzleException
     */
    public function markGreetingsAsDisplayed(array $ids): void
    {
        $url = self::BASE_URL . '/frame/images/mark/displayed';

        try {
            $response = $this->httpClient->request('POST', $url, [
                'form_params' => ['ids' => $ids],
            ]);
            $body = json_decode($response->getBody()->getContents(), true);
        } catch (Exception $e) {
            // Handle errors appropriately
            throw $e;
        }
    }

    public function checkGreetings(): bool
    {
        $url = self::BASE_URL . '/frame/'.$this->digitalFrameAccount.'/check';

        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => [],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            return $body['newImagesAvailable'];
        } catch (Exception $e) {
            // Handle errors appropriately
            throw $e;
        }
    }
}
