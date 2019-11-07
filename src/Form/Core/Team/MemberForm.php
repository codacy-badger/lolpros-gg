<?php

namespace App\Form\Core\Team;

use App\Entity\Core\Identity\Identity;
use App\Entity\Core\Team\Member;
use App\Entity\Core\Team\Team;
use App\Form\EntityTransformer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberForm extends AbstractType
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
            ->add('player', TextType::class)
            ->add('team', TextType::class)
            ->add('role', TextType::class)
            ->add('joinDate', TextType::class)
            ->add('leaveDate', TextType::class);

        $builder->get('player')->addModelTransformer(new EntityTransformer($this->entityManager->getRepository(Identity::class)));
        $builder->get('team')->addModelTransformer(new EntityTransformer($this->entityManager->getRepository(Team::class)));
        $builder->get('joinDate')->addModelTransformer(new CallbackTransformer(function ($string) { return $string; }, function ($datetime) { return new DateTime($datetime); }));
        $builder->get('leaveDate')->addModelTransformer(new CallbackTransformer(function ($string) { return $string; }, function ($datetime) { return $datetime ? new DateTime($datetime) : null; }));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Member::class,
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
