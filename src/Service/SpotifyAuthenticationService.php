<?php

namespace App\Service;

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpotifyAuthenticationService
{
    const STORE_TOKEN_URL = 'https://www.wh-company.de/spotify/callback.php';
    const FETCH_TOKEN_URL = 'https://www.wh-company.de/spotify/fetch.php';

    const CLIENT_ID = '256af412dbb24e24860bec226f803d2b';
    const CLIENT_SECRET = 'a677dfe060c942668a9472be5aa93213';
    const REDIRECT_URI = 'https://wh-company.de/spotify/callback.php';
    const OAUTH_TOKEN = 'AQAcV_ubAN4dPUbcTmHDCrYXCuBaNy3-l6XAlIGmeHSt5lKILxS9_cj0d2XCjckqKLXUVE5hTCTWLo5lddMK7gUtEH2P4r-W_8C5j2Yg372_7LkqbteV9_9WdMZZKKaS2__mlYyUBx216F4NPvXGuu0l_RQgJXwuXFOIDwsycTTqa6TdlC2How26f9cKoQRJ-VeeSDvf04O006uQ20yYtAKlTGQLBIh25gpt';
    const ACCESS_TOKEN = 'BQDQq1UuQ3B8ppVeFfeVGsbqtgLBD-Dr4VNCfSCSgEvn-PWZBX8r0KGE1bY9CSCexKvTQ1ZZIg_AqZotmVndHu4MFrfHyulUrytO9xTCb1iH5ktHWRqo5QWAIREEmS0tm9DXRFm8hKtpUS61wq0X_3_SfymvbCCH5EOUuDpfaBOlXaYPMCwAcCfdOa1G4dYqnVyRIlHm8m4';
    const REFRESH_TOKEN = 'AQDJwYpJThs9hh6WFL2IOsNx69nwsimTmz1VJ802uEB_jmDn4268Og59Bdc8iQopMolKc9EV8C6fGXWjkxrsH8so8xGhXUKw6TREt81xduS28yXjQ-bF6Tt6Lacy3tjvm-8';

    public function __construct(private HttpClientInterface $client)
    {
    }


    public function getAccessToken()
    {
        $tokenSet = $this->getTokensFromDb();
        return $tokenSet['acess'];
    }

    public function createAccessToken(){
        $session = new Session(
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::REDIRECT_URI
        );

        $session->requestAccessToken(self::OAUTH_TOKEN);

        $accessToken = $session->getAccessToken();
        $refreshToken = $session->getRefreshToken();

        $this->storeTokensToDb($accessToken, $refreshToken);

        return [$accessToken, $refreshToken];
    }


    public function getOauthToken(){
        $session = new Session(
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::REDIRECT_URI
        );

        $state = $session->generateState();
        $options = [
            'scope' => [
                'user-read-currently-playing',
            ],
            'state' => $state,
        ];

        return $session->getAuthorizeUrl($options);

    }

    public function refreshAccessToken(){
        $session = new Session(
            self::CLIENT_ID,
            self::CLIENT_SECRET,
            self::REDIRECT_URI
        );

// Fetch the refresh token from somewhere. A database for example.
        $tokenSet = $this->getTokensFromDb();

        $session->refreshAccessToken($tokenSet['refresh']);

        $accessToken = $session->getAccessToken();
        $refreshToken = $session->getRefreshToken();

        $this->storeTokensToDb($accessToken, $refreshToken);

// Set our new access token on the API wrapper and continue to use the API as usual
//        $api->setAccessToken($accessToken);
    }

    public function storeTokensToDb(string $accessToken, string $refreshToken)
    {
        $response = $this->client->request('GET', self::STORE_TOKEN_URL, [
            // these values are automatically encoded before including them in the URL
            'query' => [
                'access' => $accessToken,
                'refresh' => $refreshToken,
            ],
        ]);
    }

    public function getTokensFromDb(): array
    {
        $response = $this->client->request('GET', self::FETCH_TOKEN_URL, [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);
        return (json_decode($response->getContent(), true));
    }
}