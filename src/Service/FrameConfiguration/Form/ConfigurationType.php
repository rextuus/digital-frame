<?php

namespace App\Service\FrameConfiguration\Form;

use App\Entity\UnsplashTag;
use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Unsplash\UnsplashImageService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationType extends AbstractType
{
    public function __construct(
        private readonly UnsplashImageService $imageService,
        private readonly FrameConfigurationService $configurationService
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentTag = $this->configurationService->getCurrentTag();
        $builder->add('tag', EntityType::class, [
            'class' => UnsplashTag::class,
            'label' => '<i class="fa-solid fa-tag fa-2x"></i><br><span>Search-Tag</span>',
            'label_html' => true,
            'label_attr' => ['style' => 'margin-right: 10px', 'id' => 'tag-label'],
            'choices' => $this->imageService->getStoredTags(),
            'data' => $currentTag,

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
            'attr' => ['name' => 'spotifyInterruption']
        ]);
        $builder->add('blur', SubmitType::class, [
            'label' => '<i class="fa-solid fa-water fa-2x"></i><br><span>Blur Background</span>',
            'label_html' => true,
            'attr' => ['name' => 'spotifyInterruption']
        ]);
        $builder->add('clear', SubmitType::class, [
            'label' => '<i class="fa-solid fa-glasses fa-2x"></i><br><span>Clear Background</span>',
            'label_html' => true,
            'attr' => ['name' => 'spotifyInterruption']
        ]);
        $builder->add('maximize', SubmitType::class, [
            'label' => '<i class="fa-solid fa-maximize fa-2x"></i><br><span>Maximize</span>',
            'label_html' => true,
            'attr' => ['name' => 'spotifyInterruption']
        ]);

        $builder
            ->add('spotify', SubmitType::class, [
                'label' => '<i class="fa-brands fa-spotify fa-2x"></i><br><span>Spotify</span>',
                'label_html' => true,
                'attr' => ['name' => 'spotify']
            ])
            ->add('image', SubmitType::class, [
                'label' => '<i class="fa-brands fa-unsplash fa-2x"></i><br><span>Unsplash</span>',
                'label_html' => true,
                'attr' => ['name' => 'image']
            ])
            ->add('artsy', SubmitType::class, [
                'label' => '<i class="fa-solid fa-palette fa-2x"></i><br><span>Artsy</span>',
                'label_html' => true,
                'attr' => ['name' => 'artsy']
            ])
            ->add('greeting', SubmitType::class, [
                'label' => '<i class="fa-solid fa-image fa-2x"></i><br><span>Greeting</span>',
                'label_html' => true,
                'attr' => ['name' => 'greeting']
            ])
            ->add('nasa', SubmitType::class, [
                'label' => '<i class="fa-solid fa-shuttle-space fa-2x"></i><br><span>Nasa</span>',
                'label_html' => true,
                'attr' => ['name' => 'nasa']
            ])
            ->add('next', SubmitType::class, [
                'label' => '<i class="fa-solid fa-forward fa-2x"></i><br><span>Next</span>',
                'label_html' => true,
                'attr' => ['name' => 'next']
            ])
            ->add('store', SubmitType::class, [
                'label' => '<i class="fa-solid fa-heart fa-2x"></i><br><span>Favorite</span>',
                'label_html' => true,
                'attr' => ['name' => 'store']
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
                'label' => '<i class="fa-brands fa-spotify fa-2x"></i> <i class="fa-solid fa-bolt"></i><br><span>Interrupt</span>',
                'label_html' => true,
                'attr' => ['name' => 'spotifyInterruption']
            ])
            ->add('greetingInterruption', SubmitType::class, [
                'label' => '<i class="fa-solid fa-image fa-2x"></i> <i class="fa-solid fa-bolt"></i><br><span>Interrupt</span>',
                'label_html' => true,
                'attr' => ['name' => 'greetingInterruption']
            ]);
    }
}