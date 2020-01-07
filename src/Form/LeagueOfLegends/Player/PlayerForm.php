<?php

namespace App\Form\LeagueOfLegends\Player;

use App\Entity\LeagueOfLegends\Player;
use App\Entity\Region\Region;
use App\Form\EntityTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerForm extends AbstractType
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
            ->add('position', TextType::class)
            ->add('country', TextType::class)
            ->add('regions', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
            ]);

        $builder->get('regions')->addModelTransformer(new EntityTransformer($this->entityManager->getRepository(Region::class)));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Player::class,
            'csrf_protection' => false,
        ]);
    }

    /**
     * @return array
     */
    public static function buildOptions(string $method, array $data)
    {
        $validationGroups = [
            sprintf('league.%s_player', strtolower($method)),
        ];

        return [
            'validation_groups' => $validationGroups,
            'method' => strtoupper($method),
        ];
    }
}
