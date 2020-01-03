<?php

namespace App\Form\Core\Report;

use App\Entity\Report\AddRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddRequestForm extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('country', TextType::class)
            ->add('position', TextType::class)
            ->add('twitter', TextType::class)
            ->add('twitch', TextType::class)
            ->add('comment', TextType::class)
            ->add('summonerName', TextType::class)
            ->add('summonerId', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => AddRequest::class,
            'csrf_protection' => false,
        ]);
    }

    public static function buildOptions(string $method): array
    {
        $validationGroups = [
            sprintf('%s_add_request', strtolower($method)),
        ];

        return [
            'validation_groups' => $validationGroups,
            'method' => strtoupper($method),
        ];
    }
}
