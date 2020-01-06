<?php

namespace App\Transformer;

use App\Entity\LeagueOfLegends\Player\Ranking;
use App\Indexer\Indexer;
use DateTime;
use Elastica\Document;

class RankingTransformer extends DefaultTransformer
{
    public function fetchAndTransform($document, array $fields): ?Document
    {
        $ranking = $this->entityManager->getRepository(Ranking::class)->find($document['id']);

        if (!$ranking instanceof Ranking) {
            return null;
        }

        return $this->transform($ranking, $fields);
    }

    public function transform($ranking, array $fields)
    {
        if (!$ranking instanceof Ranking) {
            return null;
        }

        $document = [
            'created_at' => $ranking->getCreatedAt()->format(DateTime::ISO8601),
            'queue_type' => $ranking->getQueueType(),
            'season' => $ranking->getSeason(),
            'tier' => $ranking->getTier(),
            'ranking' => $ranking->getRank(),
            'league_points' => $ranking->getLeaguePoints(),
            'wins' => $ranking->getWins(),
            'losses' => $ranking->getLosses(),
            'games' => $ranking->getTotalGames(),
            'winrate' => $ranking->getWinrate(),
            'score' => $ranking->getScore(),
            'owner' => $this->buildOwner($ranking),
        ];

        return new Document($ranking->getId(), $document, Indexer::INDEX_TYPE_RANKING, Indexer::INDEX_RANKINGS);
    }

    private function buildOwner(Ranking $ranking)
    {
        $account = $ranking->getOwner();
        return [
            'uuid' => $account->getUuidAsString(),
            'player_uuid' => $account->getPlayer()->getUuidAsString(),
            'player_slug' => $account->getPlayer()->getSlug(),
        ];
    }
}
