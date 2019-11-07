<?php

namespace App\Fetcher;

use Elastica\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IdentityFetcher extends Fetcher
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

        $query = new Query($query);

        $query->setSize(1);

        return $query;
    }

    protected function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setDefaults([
            'slug' => null,
            'uuid' => null,
        ]);

        $resolver->setAllowedTypes('slug', ['string', 'null']);
        $resolver->setAllowedTypes('uuid', ['string', 'null']);

        return $resolver;
    }
}
