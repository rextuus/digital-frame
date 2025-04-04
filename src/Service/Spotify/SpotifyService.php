<?php

namespace App\Service\Spotify;

use App\Entity\SpotifyAccessToken;
use App\Repository\SpotifyAccessTokenRepository;
use DateTime;
use Exception;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SpotifyService
{
    private ?SpotifyWebAPI $api = null;

    public function __construct(
        private readonly SpotifyAccessTokenRepository $spotifyAccessTokenRepository,
        private readonly Security $security,
        #[Autowire('%env(SPOTIFY_CLIENT_ID)%')]
        private readonly string $clientId,
        #[Autowire('%env(SPOTIFY_CLIENT_SECRET)%')]
        private readonly string $clientSecret,
        #[Autowire('%env(SPOTIFY_REDIRECT_URI)%')]
        private readonly string $redirectUri
    ) {
    }

    private function getApi(): SpotifyWebAPI
    {
        if ($this->api === null) {
            $api = new SpotifyWebAPI();
            $api->setAccessToken($this->getValidAccessToken()->getAccessToken());

            $this->api = $api;
        }

        return $this->api;
    }

    public function createAccessToken(string $authorizationCode): void
    {
        $session = new Session(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri
        );

        $session->requestAccessToken($authorizationCode);

        $accessToken = $session->getAccessToken();
        $refreshToken = $session->getRefreshToken();

        $this->storeTokensToDb($accessToken, $refreshToken, $session->getTokenExpiration());
    }


    public function getOauthToken(): string
    {
        $session = new Session(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri
        );

        $state = $session->generateState();
        $options = [
            'scope' => [
                'user-library-modify',
                'user-read-playback-position',
                'user-read-recently-played',
                'user-read-playback-state',
                'user-read-currently-playing',
                'user-modify-playback-state',
                'user-read-private',
                'user-read-email',
            ],
            'state' => $state,
        ];

        return $session->getAuthorizeUrl($options);
    }

    public function storeTokensToDb(
        string $accessToken,
        string $refreshToken,
        int $expiration,
    ): SpotifyAccessToken {
        $spotifyAccessToken = new SpotifyAccessToken();
        $spotifyAccessToken->setAccessToken($accessToken);
        $spotifyAccessToken->setRefreshToken($refreshToken);
        $spotifyAccessToken->setExpirationDate($expiration);

        $this->spotifyAccessTokenRepository->save($spotifyAccessToken);

        return $spotifyAccessToken;
    }

    public function checkSpotifyIsLoggedIn(): bool
    {
        try {
            $spotifyAccessToken = $this->spotifyAccessTokenRepository->getNewestToken(
                $this->security->getUser()
            );
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    public function getValidAccessToken(): SpotifyAccessToken
    {
        $spotifyAccessToken = $this->spotifyAccessTokenRepository->getNewestToken();

        // check if current token is valid
        $currentTime = (new DateTime())->getTimestamp();
        $expirationDate = $spotifyAccessToken->getExpirationDate();
        if ($currentTime < $expirationDate) {
            return $spotifyAccessToken;
        }

        $session = new Session(
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri
        );

        $session->setAccessToken($spotifyAccessToken->getAccessToken());
        $session->setRefreshToken($spotifyAccessToken->getRefreshToken());

        // refresh access token and store it in db
        $session->refreshAccessToken();

        // save the new accessToken with same as in the expired one
        return $this->storeTokensToDb(
            $session->getAccessToken(),
            $session->getRefreshToken(),
            $session->getTokenExpiration(),
        );
    }

    public function getImageUrlOfCurrentlyPlayingSong(): array
    {
        try {
            $api = new SpotifyWebAPI();
            $api->setAccessToken($this->getValidAccessToken()->getAccessToken());
            $currentTrack = json_decode(json_encode($api->getMyCurrentTrack()), true);
            if ($currentTrack === null || !array_key_exists('item', $currentTrack)) {
                return [];
            }
            return [
                'url' => $currentTrack['item']['album']['images'][0]['url'],
                'name' => $currentTrack['item']['name'],
                'album' => $currentTrack['item']['album']['name'],
                'artist' => $currentTrack['item']['artists'][0]['name'],
            ];
        } catch (Exception $e) {
            return ['url' => 'spotify_error'];
        }
    }

    public function getLoginInformation(): string
    {
        $api = $this->getApi();

        $me = $api->me();

        return $me->display_name;
    }
}