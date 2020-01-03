<?php

namespace App\Form\Core\Region;

use App\Entity\Region\Region;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('shorthand', TextType::class)
            ->add('countries', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ])
            ->add('logo', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Region::class,
            'csrf_protection' => false,
        ]);
    }

    public static function buildOptions(string $method): array
    {
        $validationGroups = [
            sprintf('league.%s_region', strtolower($method)),
        ];

        return [
            'validation_groups' => $validationGroups,
            'method' => strtoupper($method),
        ];
    }
}
