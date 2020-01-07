<?php

namespace App\Transformer;

use App\Entity\LeagueOfLegends\SummonerName;
use App\Indexer\Indexer;
use DateTime;
use Elastica\Document;

class SummonerNameTransformer extends DefaultTransformer
{
    public function fetchAndTransform($document, array $fields): ?Document
    {
        $summonerName = $this->entityManager->getRepository(SummonerName::class)->findOneBy(['uuid' => $document['uuid']]);

        /** @var SummonerName $summonerName */
        if (!$summonerName instanceof SummonerName) {
            return null;
        }

        return $this->transform($summonerName, $fields);
    }

    public function transform($summonerName, array $fields)
    {
        /** @var SummonerName $summonerName */
        if (!$summonerName instanceof SummonerName) {
            return null;
        }

        $profile = $summonerName->getPlayer()->getProfile();
        $document = [
            'name' => $summonerName->getName(),
            'current' => $summonerName->isCurrent(),
            'created_at' => $summonerName->getCreatedAt()->format(DateTime::ISO8601),
            'previous' => $summonerName->getPrevious() ? $summonerName->getPrevious()->getName() : null,
            'player' => [
                'uuid' => $profile->getUuidAsString(),
                'name' => $profile->getName(),
                'slug' => $profile->getSlug(),
                'country' => $profile->getCountry(),
            ],
        ];

        return new Document($summonerName->getName().'-'.$summonerName->getCreatedAt()->format(DateTime::ISO8601), $document, Indexer::INDEX_TYPE_SUMMONER_NAME, Indexer::INDEX_SUMMONER_NAMES);
    }
}
