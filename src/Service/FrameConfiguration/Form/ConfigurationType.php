<?php

namespace App\Service\FrameConfiguration\Form;

use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Unsplash\UnsplashImageService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationType extends AbstractType
{


    public function __construct(private UnsplashImageService $imageService, private FrameConfigurationService $configurationService)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        $builder->add('mode', ChoiceType::class, [
//            'choices'  => [
//                'Image' => 1,
//                'Spotify' => 2,
//            ],
//        ]);
        $tags = array_map(function ($tag){
            return $tag['tag'];
        }, $this->imageService->getStoredTags());
        $choices = [];
        $currentTag = $this->configurationService->getCurrentTag();
        $choices[$currentTag] = $currentTag;
        $choices['random'] = 'random';
        foreach ($tags as $tag){
            $choices[$tag] = $tag;
        }
        if (empty($choices)){
            $choices['random'] = 'random';
        }

        $builder
            ->add('spotify', SubmitType::class, [
                'label' => '<i class="fa-brands fa-spotify fa-2x"></i><br><span>Spotify</span>',
                'label_html' => true,
                'attr' => ['class' => 'btn btn-disabled', 'name' => 'spotify']
            ])
            ->add('image', SubmitType::class, [
                'label' => '<i class="fa-brands fa-unsplash fa-2x"></i><br><span>Unsplash</span>',
                'label_html' => true,
                'attr' => ['class' => 'btn btn-disabled', 'name' => 'image']
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
            ->add('tag', ChoiceType::class, [
                'label' => '<i class="fa-solid fa-tag fa-2x"></i><br><span>Search-Tag</span>',
                'label_html' => true,
                'label_attr' => ['style' => 'margin-right: 10px', 'id' => 'tag-label'],
                'choices' => $choices
            ]);
    }
}