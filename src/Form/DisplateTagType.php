<?php

namespace App\Form;

use App\Entity\SearchTag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DisplateTagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var array<SearchTag> $tags */
        $tags = $options['tags'];

        $builder
            // Dropdown for existing tags
            ->add('existingTag', ChoiceType::class, [
                'choices' => $tags, // Existing tags
                'choice_label' => function (SearchTag $tag) {
                    return $tag->getTerm(); // Display name of the tag
                },
                'choice_value' => 'id', // Use the id of the tag for the value
                'placeholder' => 'Select an existing tag',
                'required' => false, // Allow the user to leave it empty if adding a new tag
            ])
            // Text field for adding a new tag
            ->add('newTag', TextType::class, [
                'required' => false, // Not required because the user may select from the dropdown
                'data' => $options['searchTerm'],
                'attr' => [
                    'placeholder' => 'Or enter a new tag name',
                ],
            ]);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DisplateTagData::class,
            'tags' => [],
            'searchTerm' => 'Select an existing tag'
        ]);

        $resolver->setRequired('tags');
        $resolver->setAllowedTypes('tags', 'array');
        $resolver->setAllowedTypes('searchTerm', 'string');
    }
}
