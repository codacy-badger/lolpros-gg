<?php

namespace App\Form\Core\Player;

use App\Entity\Core\Player\Staff;
use App\Entity\Core\Region\Region;
use App\Form\EntityTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StaffForm extends AbstractType
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
            ->add('role', TextType::class)
            ->add('roleName', TextType::class)
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
            'data_class' => Staff::class,
            'csrf_protection' => false,
        ]);
    }

    public static function buildOptions(string $method, array $data)
    {
        $validationGroups = [
            sprintf('%s_staff', strtolower($method)),
        ];

        return [
            'validation_groups' => $validationGroups,
            'method' => strtoupper($method),
        ];
    }
}
