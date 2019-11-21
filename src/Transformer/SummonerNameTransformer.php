<?php

namespace App\Transformer;

use App\Entity\LeagueOfLegends\Player\SummonerName;
use App\Indexer\Indexer;
use DateTime;
use Elastica\Document;

class SummonerNameTransformer extends DefaultTransformer
{
    public function fetchAndTransform($document, array $fields): ?Document
    {
        $team = $this->entityManager->getRepository(SummonerName::class)->findOneBy(['uuid' => $document['uuid']]);

        if (!$team instanceof SummonerName) {
            return null;
        }

        return $this->transform($team, $fields);
    }

    public function transform($summonerName, array $fields)
    {
        if (!$summonerName instanceof SummonerName) {
            return null;
        }

        $document = [
            'name' => $summonerName->getName(),
            'current' => $summonerName->isCurrent(),
            'created_at' => $summonerName->getCreatedAt()->format(DateTime::ISO8601),
            'previous' => $summonerName->getPrevious() ? $summonerName->getPrevious()->getName() : null,
            'player' => [
                'uuid' => $summonerName->getPlayer()->getUuidAsString(),
                'name' => $summonerName->getPlayer()->getName(),
                'slug' => $summonerName->getPlayer()->getSlug(),
                'country' => $summonerName->getPlayer()->getCountry(),
            ],
        ];

        return new Document($summonerName->getName().'-'.$summonerName->getCreatedAt()->format(DateTime::ISO8601), $document, Indexer::INDEX_TYPE_SUMMONER_NAME, Indexer::INDEX_SUMMONER_NAMES);
    }
}
