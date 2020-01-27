<?php

namespace App\Fetcher;

use DateTime;
use Elastica\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberFetcher extends Fetcher
{
    const SORT_JOIN = 'join';
    const SORT_LEAVE = 'leave';

    protected function createQuery(array $options): Query
    {
        $query = new Query\BoolQuery();

        if ($options['uuid']) {
            $query->addMust(new Query\MatchPhrase('uuid', $options['uuid']));
        }
        if ($options['profile']) {
            $query->addMust(new Query\MatchPhrase('profile.uuid', $options['profile']));
        }
        if ($options['team']) {
            $query->addMust(new Query\MatchPhrase('team.uuid', $options['team']));
        }
        if (null !== $options['current']) {
            $query->addMust(new Query\Term(['current' => $options['current']]));
        }
        if ($options['start'] && $options['end']) {
            $boolQuery = new Query\BoolQuery();
            $boolQuery->addMustNot(new Query\Range('leave_timestamp', ['lte' => $options['start']]));
            $boolQuery->addMustNot(new Query\Match('leave_timestamp', $options['start']));
            $boolQuery->addMustNot(new Query\Match('join_timestamp', $options['end']));
            $boolQuery->addMustNot(new Query\Range('join_timestamp', ['gte' => $options['end']]));
            $query->addMust($boolQuery);
        }

        $query = new Query($query);

        switch ($options['sort']) {
            case self::SORT_JOIN:
                $query->setSort(['join_date' => ['order' => $options['order']]]);
                break;
            case self::SORT_LEAVE:
                $query->setSort(['leave_date' => ['order' => $options['order']]]);
                break;
            default:
                $query->setSort(['join_date' => 'desc', 'leave_date' => 'asc']);
                break;
        }

        $query->setSize($options['per_page']);
        $query->setFrom(($options['page'] - 1) * $options['per_page']);

        return $query;
    }

    protected function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setDefaults([
            'per_page' => 50,
            'page' => 1,
            'sort' => null,
            'order' => 'desc',
            'uuid' => null,
            'current' => null,
            'profile' => null,
            'team' => null,
            'start' => null,
            'end' => (new DateTime())->getTimestamp(),
        ]);

        $resolver->setAllowedTypes('per_page', 'integer');
        $resolver->setAllowedTypes('page', 'integer');
        $resolver->setAllowedTypes('sort', ['string', 'null']);
        $resolver->setAllowedTypes('order', 'string');
        $resolver->setAllowedTypes('uuid', ['string', 'null']);
        $resolver->setAllowedTypes('current', ['boolean', 'null']);
        $resolver->setAllowedTypes('profile', ['string', 'null']);
        $resolver->setAllowedTypes('team', ['string', 'null']);
        $resolver->setAllowedTypes('start', ['integer', 'null']);
        $resolver->setAllowedTypes('end', ['integer']);

        return $resolver;
    }
}
