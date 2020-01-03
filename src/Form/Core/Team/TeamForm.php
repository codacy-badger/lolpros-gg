<?php

namespace App\Form\Core\Team;

use App\Entity\Region\Region;
use App\Entity\Team\Team;
use App\Form\EntityTransformer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamForm extends AbstractType
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
            ->add('tag', TextType::class)
            ->add('creationDate', TextType::class)
            ->add('disbandDate', TextType::class)
            ->add('region', TextType::class);

        $builder->get('region')->addModelTransformer(new EntityTransformer($this->entityManager->getRepository(Region::class)));
        $builder->get('creationDate')->addModelTransformer(new CallbackTransformer(function ($string) { return $string; }, function ($datetime) { return new DateTime($datetime); }));
        $builder->get('disbandDate')->addModelTransformer(new CallbackTransformer(function ($string) { return $string; }, function ($datetime) { return $datetime ? new DateTime($datetime) : null; }));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Team::class,
            'csrf_protection' => false,
        ]);
    }

    public static function buildOptions(string $method, array $data): array
    {
        $validationGroups = [
            sprintf('%s_team', strtolower($method)),
        ];

        return [
            'validation_groups' => $validationGroups,
            'method' => strtoupper($method),
        ];
    }
}
