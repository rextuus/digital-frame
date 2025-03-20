<?php

declare(strict_types=1);

namespace App\Service\Synchronization;

use App\Service\Greeting\Form\GreetingCreateData;
use App\Service\Synchronization\Exception\GreetingApiException;
use App\Service\Util\SentryService;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DigitalFrameApiGateway
{
    private Client $httpClient;

    public function __construct(
        private readonly SentryService $sentryService,
        #[Autowire('%env(GREETING_API_BASE_URL)%')]
        private readonly string $greetingApiBaseUrl,
        #[Autowire('%env(DIGITAL_FRAME_ACCOUNT)%')]
        private readonly string $digitalFrameAccount
    ) {
        $this->httpClient = new Client();
    }

    /**
     * @return array<GreetingCreateData>
     */
    public function getGreetings(): array
    {
        $url = $this->greetingApiBaseUrl . '/frame/'.$this->digitalFrameAccount.'/synchronize/images';

        $queryParams = [];

        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => $queryParams,
            ]);

            $greetingDatas = [];
            $body = json_decode($response->getBody()->getContents(), true);
            foreach ($body['images'] as $image){
                $data = new GreetingCreateData();
                $data->setUploaded(new DateTime($image['uploadedToCdn']));
                $data->setCdnUrl($image['cdnUrl']);
                $data->setName($image['name']);
                $data->setRemoteId($image['id']);
                $data->setDelivered(new DateTime());
                $data->setLastSynced(new DateTime());

                $greetingDatas[] = $data;
            }

            return $greetingDatas;
        } catch (Exception|GuzzleException $e) {
            $exception = new GreetingApiException('Failed to get newest greetings from server', 500, $e);
            $this->sentryService->captureException($exception);

            return [];
        }
    }

    /**
     * @param array<int> $ids
     */
    public function markGreetingsAsDelivered(array $ids): void
    {
        $url = $this->greetingApiBaseUrl . '/frame/images/mark/delivered';

        try {
            $response = $this->httpClient->request('POST', $url, [
                'form_params' => ['ids' => $ids],
            ]);
            json_decode($response->getBody()->getContents(), true);
        } catch (Exception|GuzzleException $e) {
            $exception = new GreetingApiException('Failed to mark greetings as delivered', 500, $e);
            $this->sentryService->captureException($exception);
        }
    }

    /**
     * @param array<int> $ids
     */
    public function markGreetingsAsDisplayed(array $ids): void
    {
        $url = $this->greetingApiBaseUrl . '/frame/images/mark/displayed';

        try {
            $response = $this->httpClient->request('POST', $url, [
                'form_params' => ['ids' => $ids],
            ]);
            json_decode($response->getBody()->getContents(), true);
        } catch (Exception|GuzzleException $e) {
            $exception = new GreetingApiException('Failed to mark greetings as displayed', 500, $e);
            $this->sentryService->captureException($exception);
        }
    }

    public function checkGreetings(): bool
    {
        $url = $this->greetingApiBaseUrl . '/frame/'.$this->digitalFrameAccount.'/check';

        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => [],
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            return $body['newImagesAvailable'];
        } catch (Exception $e) {
            $exception = new GreetingApiException('Failed to mark greetings as displayed', 500, $e);
            $this->sentryService->captureException($exception);

            return false;
        }
    }
}
