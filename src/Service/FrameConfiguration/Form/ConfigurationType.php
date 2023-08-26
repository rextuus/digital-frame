<?php

namespace App\Service\FrameConfiguration\Form;

use App\Service\FrameConfiguration\FrameConfigurationService;
use App\Service\Image\ImageService;
use App\Service\Image\Unsplash\UnsplashImageService;
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
            ->add('spotify', SubmitType::class, ['label' => 'Switch to Spotify', 'attr' => ['class' => 'btn btn-disabled']])
            ->add('image', SubmitType::class, ['label' => 'Switch to Unsplash', 'attr' => ['class' => 'btn btn-disabled']])
            ->add('greeting', SubmitType::class, ['label' => 'Switch to Greetings', 'attr' => ['class' => 'btn btn-disabled']])
            ->add('next', SubmitType::class, ['label' => 'Next', 'attr' => ['class' => 'btn btn-disabled']])
            ->add('store', SubmitType::class, ['label' => 'Store in DB', 'attr' => ['class' => 'btn btn-enabled']])
            ->add('newTag', TextType::class, ['label' => false, 'required' => false, 'attr' => ['class' => '']])
            ->add(
                'tag',
                ChoiceType::class,
                [
                    'label' => 'Select tag',
                    'attr' => ['class' => 'btn btn-enabled'],
                    'choices' => $choices
                ]);
    }
}