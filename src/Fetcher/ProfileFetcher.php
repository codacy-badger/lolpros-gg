<?php

namespace App\Fetcher;

use Elastica\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileFetcher extends Fetcher
{
    protected function createQuery(array $options): Query
    {
        $query = new Query\BoolQuery();

        if ($options['slug']) {
            $query->addMust(new Query\MatchPhrase('slug', $options['slug']));
        }
        if ($options['uuid']) {
            $query->addMust(new Query\MatchPhrase('uuid', $options['uuid']));
        }
        if ($options['riot_id']) {
            $nested = new Query\Nested();
            $nested->setPath('league_player');
            $nested2 = new Query\Nested();
            $nested2->setPath('league_player.accounts');
            $nested2->setQuery(new Query\MatchPhrase('league_player.accounts.encrypted_riot_id', $options['riot_id']));
            $query->addMust($nested2);
        }

        $query = new Query($query);

        $query->setSize(1);

        return $query;
    }

    protected function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setDefaults([
            'slug' => null,
            'uuid' => null,
            'riot_id' => null,
        ]);

        $resolver->setAllowedTypes('slug', ['string', 'null']);
        $resolver->setAllowedTypes('uuid', ['string', 'null']);
        $resolver->setAllowedTypes('riot_id', ['string', 'null']);

        return $resolver;
    }
}
