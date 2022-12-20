<?php

namespace App\Service\FrameConfiguration\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
//        $builder->add('mode', ChoiceType::class, [
//            'choices'  => [
//                'Image' => 1,
//                'Spotify' => 2,
//            ],
//        ]);
        $builder
            ->add('spotify', SubmitType::class, ['label' => 'Switch to Spotify', 'attr' => ['class' => 'btn btn-disabled']])
            ->add('image', SubmitType::class, ['label' => 'Switch to Unsplash', 'attr' => ['class' => 'btn btn-disabled']])
            ->add('next', SubmitType::class, ['label' => 'Next', 'attr' => ['class' => 'btn btn-disabled']])
            ->add('store', SubmitType::class, ['label' => 'Store in DB', 'attr' => ['class' => 'btn btn-enabled']]);
    }
}