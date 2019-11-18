<?php

namespace App\Fetcher;

use Elastica\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamFetcher extends Fetcher
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
        if (null !== $options['active']) {
            $query->addMust(new Query\Term(['active' => $options['active']]));
        }
        if (null !== $options['query']) {
            $searchQuery = new Query\BoolQuery();
            $fullNameQuery = new Query\Wildcard('name', '*'.$options['query'].'*');
            $nameQuery = new Query\Wildcard('name', $options['query'].'*', 2);
            $tagQuery = new Query\Wildcard('tag', '*'.$options['query'].'*');
            $slugQuery = new Query\Wildcard('slug', '*'.$options['query'].'*');

            $searchQuery->addShould([$fullNameQuery, $nameQuery, $tagQuery, $slugQuery]);
            $query->addMust($searchQuery);
        }

        $query = new Query($query);

        $query->setSize($options['per_page']);
        $query->setFrom(($options['page'] - 1) * $options['per_page']);

        return $query;
    }

    protected function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setDefaults([
            'per_page' => 15,
            'page' => 1,
            'slug' => null,
            'uuid' => null,
            'active' => null,
            'query' => null,
        ]);

        $resolver->setAllowedTypes('per_page', 'integer');
        $resolver->setAllowedTypes('page', 'integer');
        $resolver->setAllowedTypes('slug', ['string', 'null']);
        $resolver->setAllowedTypes('uuid', ['string', 'null']);
        $resolver->setAllowedTypes('query', ['string', 'null']);
        $resolver->setAllowedTypes('active', ['boolean', 'null']);

        return $resolver;
    }
}
