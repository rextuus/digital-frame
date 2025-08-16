<?php

namespace App\Service\FrameConfiguration\Form;

use App\Entity\FavoriteList;
use App\Entity\SearchTag;
use App\Service\Favorite\FavoriteService;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Unsplash\UnsplashImageService;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationType extends AbstractType
{
    public function __construct(
        private readonly UnsplashImageService $imageService,
        private readonly FrameConfigurationService $configurationService,
        private readonly FavoriteService $favoriteService,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentTag = $this->configurationService->getCurrentTag();
        $builder->add('tag', EntityType::class, [
            'class' => SearchTag::class,
            'label' => '<i class="fa-solid fa-tag fa-2x"></i><br><span>Search-Tag</span>',
            'label_html' => true,
            'label_attr' => ['style' => 'margin-right: 10px', 'id' => 'tag-label'],
            'choices' => $this->imageService->getStoredTags(),
            'data' => $currentTag,

        ]);

        $favoriteLists = $this->favoriteService->getFavoriteListsForTarget();
        if (count($favoriteLists) === 0) {
            $favoriteLists[] = $this->favoriteService->getDefaultFavoriteList();
        }
        $builder->add('favoriteList', EntityType::class, [
            'class' => FavoriteList::class,
            'label' => '<i class="fa-solid fa-list fa-2x"></i><br><span></span>',
            'label_html' => true,
            'label_attr' => ['style' => 'margin-right: 10px', 'id' => 'tag-label'],
            'choices' => $favoriteLists,
            'data' => $favoriteLists[0],
        ]);

        $builder->add('color', ColorType::class, [
            'label' => false,
            'label_html' => true,
            'required' => false,
            'attr' => [
                'class' => 'form-control-color',
            ]
        ]);
        $builder->add('changeColor', SubmitType::class, [
            'label' => '<i class="fa-solid fa-palette fa-2x"></i><br><span>Change color</span>',
            'label_html' => true,
        ]);
        $builder->add('blur', SubmitType::class, [
            'label' => '<i class="fa-solid fa-water fa-2x"></i><br><span>Blur Background</span>',
            'label_html' => true,
        ]);
        $builder->add('clear', SubmitType::class, [
            'label' => '<i class="fa-solid fa-glasses fa-2x"></i><br><span>Clear Background</span>',
            'label_html' => true,
        ]);
        $builder->add('maximize', SubmitType::class, [
            'label' => '<i class="fa-solid fa-maximize fa-2x"></i><br><span>Maximize</span>',
            'label_html' => true,
        ]);
        $builder->add('customHeight', SubmitType::class, [
            'label' => '<i class="fa-solid fa-sort-numeric-up fa-2x"></i><br><span>CustomHeight</span>',
            'label_html' => true,
        ]);
        $builder->add('height', NumberType::class, [
            'label' => false,
            'label_html' => true,
            'required' => false,
        ]);
        $builder->add('margin', NumberType::class, [
            'label' => false,
            'label_html' => true,
            'required' => false,
        ]);

        $builder
            ->add('spotify', SubmitType::class, [
                'label' => '<i class="fa-brands fa-spotify fa-2x"></i><br><span>Spotify</span>',
                'label_html' => true,
            ])
            ->add('image', SubmitType::class, [
                'label' => '<i class="fa-brands fa-unsplash fa-2x"></i><br><span>Unsplash</span>',
                'label_html' => true,
            ])
            ->add('artsy', SubmitType::class, [
                'label' => '<i class="fa-solid fa-palette fa-2x"></i><br><span>Artsy</span>',
                'label_html' => true,
            ])
            ->add('greeting', SubmitType::class, [
                'label' => '<i class="fa-solid fa-image fa-2x"></i><br><span>Greeting</span>',
                'label_html' => true,
            ])
            ->add('nasa', SubmitType::class, [
                'label' => '<i class="fa-solid fa-shuttle-space fa-2x"></i><br><span>Nasa</span>',
                'label_html' => true,
            ])
            ->add('displate', SubmitType::class, [
                'label' => '<i class="fa-solid fa-d fa-2x"></i><br><span>Displate</span>',
                'label_html' => true,
            ])
            ->add('next', SubmitType::class, [
                'label' => '<i class="fa-solid fa-forward fa-2x"></i><br><span>Next</span>',
                'label_html' => true,
            ])
            ->add('store', SubmitType::class, [
                'label' => '<i class="fa-solid fa-heart fa-2x"></i><br><span>Favorite</span>',
                'label_html' => true,
            ])
            ->add('newTag', TextType::class, [
                'label' => false,
                'required' => false,
            ])
//            ->add('tag', ChoiceType::class, [
//                'label' => '<i class="fa-solid fa-tag fa-2x"></i><br><span>Search-Tag</span>',
//                'label_html' => true,
//                'label_attr' => ['style' => 'margin-right: 10px', 'id' => 'tag-label'],
//                'choices' => $choices
//            ])
            ->add('spotifyInterruption', SubmitType::class, [
                'label' => '<i class="fa-brands fa-spotify fa-2x"></i> <i class="fa-solid fa-bolt text-warning"></i><br><span>Interrupt</span>',
                'label_html' => true,
            ])
            ->add('greetingInterruption', SubmitType::class, [
                'label' => '<i class="fa-solid fa-image fa-2x"></i> <i class="fa-solid fa-bolt text-warning"></i><br><span>Interrupt</span>',
                'label_html' => true,
            ]);
    }
}