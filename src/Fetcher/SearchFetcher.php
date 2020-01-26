<?php

namespace App\Fetcher;

use Elastica\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFetcher extends Fetcher
{
    protected function createQuery(array $options): Query
    {
        $query = new Query\BoolQuery();

        $nested = new Query\Nested();
        $nested->setPath('league_player');
        $nested2 = new Query\Nested();
        $nested2->setPath('league_player.accounts');
        $nested2->setQuery(new Query\Wildcard('league_player.accounts.summoner_name', '*'.$options['query'].'*'));
        $query->addShould($nested2);
        $query->addShould(new Query\Wildcard('name', $options['query'].'*', 2));
        $query->addShould(new Query\Wildcard('name', '*'.$options['query'].'*'));

        $query = new Query($query);
        $query->setSize($options['per_page']);
        $query->setFrom(($options['page'] - 1) * $options['per_page']);

        return $query;
    }

    protected function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setDefaults([
            'per_page' => 50,
            'page' => 1,
            'query' => null,
        ]);

        $resolver->setAllowedTypes('query', ['string', 'null']);

        return $resolver;
    }
}
