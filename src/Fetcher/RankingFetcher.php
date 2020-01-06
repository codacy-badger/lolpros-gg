<?php

namespace App\Fetcher;

use Elastica\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RankingFetcher extends Fetcher
{
    const SELECT_CURRENT = 'current';
    const SELECT_BEST = 'best';

    protected function createQuery(array $options): Query
    {
        $query = new Query\BoolQuery();

        if ($options['uuid']) {
            $query->addMust(new Query\MatchPhrase('owner.uuid', $options['uuid']));
        }
        if ($options['player_uuid']) {
            $query->addMust(new Query\MatchPhrase('owner.player_uuid', $options['player_uuid']));
        }
        if ($options['player_slug']) {
            $query->addMust(new Query\MatchPhrase('owner.player_slug', $options['player_slug']));
        }
        if ($options['season']) {
            $query->addMust(new Query\Term(['season' => $options['season']]));
        }

        if ($options['start'] && $options['end']) {
            $query->addMust(new Query\Range('created_at', ['lte' => $options['end'], 'gte' => $options['start']]));
        }

        $query = new Query($query);

        switch ($options['select']) {
            case self::SELECT_CURRENT:
                $query->setSize(1);
                $query->setSort(['created_at' => 'desc']);
                break;
            case self::SELECT_BEST:
                $query->setSize(1);
                $query->setSort(['score' => 'desc']);
                break;
            default:
                $query->setSize($options['per_page']);
                $query->setSort(['created_at' => 'desc']);
                break;
        }

        return $query;
    }

    protected function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setDefaults([
            'per_page' => 50,
            'page' => 1,
            'select' => null,

            'uuid' => null,
            'player_uuid' => null,
            'player_slug' => null,
            'season' => null,

            'start' => null,
            'end' => null,
        ]);

        $resolver->setAllowedTypes('per_page', 'integer');
        $resolver->setAllowedTypes('page', 'integer');

        $resolver->setAllowedTypes('uuid', ['string', 'null']);
        $resolver->setAllowedTypes('player_uuid', ['string', 'null']);
        $resolver->setAllowedTypes('player_slug', ['string', 'null']);
        $resolver->setAllowedTypes('select', ['string', 'null']);
        $resolver->setAllowedTypes('season', ['string', 'null']);

        $resolver->setAllowedTypes('start', ['integer', 'null']);
        $resolver->setAllowedTypes('end', ['integer', 'null']);

        return $resolver;
    }
}
