<?php

namespace App\Manager\LeagueOfLegends\Riot;

use App\Entity\LeagueOfLegends\Ranking;
use RiotAPI\LeagueAPI\Definitions\Region;
use RiotAPI\LeagueAPI\LeagueAPI;
use RiotAPI\LeagueAPI\Objects\LeagueEntryDto;
use RiotAPI\LeagueAPI\Objects\LeagueListDto;

class RiotLeagueManager
{
    /**
     * @var LeagueAPI
     */
    private $api;

    public function __construct(string $apiKey)
    {
        $this->api = new LeagueAPI([
            LeagueAPI::SET_KEY => $apiKey,
            LeagueAPI::SET_REGION => Region::EUROPE_WEST,
            LeagueAPI::SET_VERIFY_SSL => false,
        ]);
    }

    public function getForId(string $id): ?LeagueEntryDto
    {
        $leagues = $this->api->getLeagueEntriesForSummoner($id);

        $soloQ = array_filter($leagues, function ($league) {
            /* @var LeagueEntryDto $league */
            return $league->queueType && Ranking::QUEUE_TYPE_SOLO === $league->queueType;
        });

        if (!count($soloQ)) {
            return null;
        }

        return array_key_exists(0, $soloQ) ? $soloQ[0] : reset($soloQ);
    }

    public function getChallengers(): LeagueListDto
    {
        return $this->api->getLeagueChallenger(Ranking::QUEUE_TYPE_SOLO);
    }
}
