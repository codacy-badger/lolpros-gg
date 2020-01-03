<?php

namespace App\Fetcher;

use App\Entity\LeagueOfLegends\LeaguePlayer;
use Elastica\Query;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LadderFetcher extends Fetcher
{
    const SORT_RANK = 'rank';
    const SORT_GAMES = 'games';
    const SORT_WINRATE = 'winrate';
    const SORT_PEAK = 'peak';
    const SORT_PEAK_DATE = 'peak_date';
    const SORT_TOTAL_GAMES = 'total_games';

    protected function createQuery(array $options): Query
    {
        $query = new Query\BoolQuery();

        if (null !== $options['position']) {
            $query->addMust(new Query\Match('position', $options['position']));
        }
        if (null !== $options['country']) {
            $query->addMust(new Query\Match('country', $options['country']));
        }
        if (null !== $options['region']) {
            $regionQuery = new Query\Nested();
            $regionQuery->setPath('regions');
            $boolQuery = new Query\BoolQuery();
            $boolQuery->addMust(new Query\Match('regions.shorthand', $options['region']));
            $regionQuery->setQuery($boolQuery);
            $query->addMust($regionQuery);
        }

        switch ($options['team']) {
            case 'false':
                $query->addMustNot(new Query\Exists('team')); break;
            case 'true':
                $query->addMust(new Query\Exists('team')); break;
            case null:
                break;
            default:
                $query->addMust(new Query\Match('team.slug', $options['team'])); break;
        }

        $query = new Query($query);

        switch ($options['sort']) {
            case self::SORT_GAMES:
                $query->setSort(['account.games' => ['order' => $options['order'], 'nested_path' => 'account'], 'name' => 'asc']);
                break;
            case self::SORT_TOTAL_GAMES:
                $query->setSort(['total_games' => ['order' => $options['order']], 'name' => 'asc']);
                break;
            case self::SORT_WINRATE:
                $query->setSort(['account.winrate' => ['order' => $options['order'], 'nested_path' => 'account'], 'name' => 'asc']);
                break;
            case self::SORT_PEAK:
                $query->setSort(['peak.score' => ['order' => $options['order'], 'nested_path' => 'peak'], 'name' => 'asc']);
                break;
            case self::SORT_PEAK_DATE:
                $query->setSort(['peak.date' => ['order' => $options['order'], 'nested_path' => 'peak'], 'name' => 'asc']);
                break;
            case self::SORT_RANK:
            default:
                $query->setSort(['score' => $options['order'], 'name' => 'asc']);
                break;
        }

        $query->setPostFilter(new Query\Range('score', ['gt' => 2705]));
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
            'position' => null,
            'country' => null,
            'region' => null,
            'team' => null,
        ]);

        $resolver->setAllowedTypes('per_page', 'integer');
        $resolver->setAllowedTypes('page', 'integer');
        $resolver->setAllowedTypes('sort', ['string', 'null']);
        $resolver->setAllowedTypes('order', 'string');
        $resolver->setAllowedTypes('position', ['string', 'null']);
        $resolver->setAllowedValues('position', array_merge(Player::getAvailablePositions(), [null]));
        $resolver->setAllowedTypes('country', ['string', 'null']);
        $resolver->setAllowedTypes('region', ['string', 'null']);
        $resolver->setAllowedTypes('team', ['bool', 'string', 'null']);

        return $resolver;
    }
}
