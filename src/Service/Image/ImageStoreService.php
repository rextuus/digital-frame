<?php

namespace App\Service\Image;

use App\Entity\FrameConfiguration;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Image\Unsplash\UnsplashApiService;
use App\Service\Image\Unsplash\UnsplashImageService;
use App\Service\SpotifyAuthenticationService;
use Symfony\Component\HttpKernel\KernelInterface;

class ImageStoreService
{
    public function __construct(
        private ImageService                 $imageService,
        private FrameConfigurationService    $configurationService,
        private SpotifyAuthenticationService $spotifyAuthenticationService,
        private UnsplashImageService           $unsplashService,
        private KernelInterface              $appKernel
    )
    {
    }

    public function storeCurrentlyDisplayedImage(){
        $mode = $this->configurationService->getMode();

        $imageData = new ImageData();
        if ($mode == FrameConfiguration::MODE_UNSPLASH){
            $image = $this->unsplashService->getNextRandomImage();
            dd($image);
        }
        if ($mode == FrameConfiguration::MODE_SPOTIFY){
            // get album url
            $imageInfo = $this->spotifyAuthenticationService->getImageUrlOfCurrentlyPlayingSong();
            if (empty($imageInfo)){
                return;
            }
            $imageData->setUrl($imageInfo['url']);
            // define name and path
            $newFilename = str_replace(' ', '_', $imageInfo['name']).'-'.uniqid().'.jpeg';
            $path = $this->appKernel->getProjectDir().'/public/images/spotify/'.$newFilename;
            $this->imageService->downloadImage($imageInfo['url'], $path);
        }
    }
}