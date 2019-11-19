<?php

namespace App\Form\LeagueOfLegends\Player;

use App\Entity\LeagueOfLegends\Player\RiotAccount;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RiotAccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('smurf', CheckboxType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => RiotAccount::class,
            'csrf_protection' => false,
        ]);
    }

    /**
     * @return array
     */
    public static function buildOptions(string $method, array $data)
    {
        $validationGroups = [
            sprintf('league.%s_riot_account', strtolower($method)),
        ];

        return [
            'validation_groups' => $validationGroups,
            'method' => strtoupper($method),
        ];
    }
}
