<?php

namespace App\Form;

use App\Service\Scheduling\ScheduleAction;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleFrameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $actions = ScheduleAction::cases();

        /** @var array<string> $identifiers */
        $identifiers = $options['identifiers'];

        $builder
            // Dropdown for existing tags
            ->add('identifier', ChoiceType::class, [
                'choices' => array_combine($identifiers, $identifiers),
                'required' => true, // Allow the user to leave it empty if adding a new tag
            ])
            ->add('fromHour', NumberType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'HH',
                    'maxlength' => 2,
                ],
            ])
            ->add('fromMinute', NumberType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'MM',
                    'maxlength' => 2,
                ],
            ])
            ->add('toHour', NumberType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'HH',
                    'maxlength' => 2,
                ],
            ])
            ->add('toMinute', NumberType::class, [
                'required' => true,
                'attr' => [
                    'placeholder' => 'MM',
                    'maxlength' => 2,
                ],
            ])
            ->add('interval', NumberType::class, [
                'required' => true,
            ])
            ->add('action', ChoiceType::class, [
                'choices' => $actions,
                'choice_label' => fn(ScheduleAction $action) => $action->name,
                'choice_value' => fn(?ScheduleAction $action) => $action ? $action->name : '',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ScheduleFrameData::class,
            'identifiers' => [],
        ]);
    }
}