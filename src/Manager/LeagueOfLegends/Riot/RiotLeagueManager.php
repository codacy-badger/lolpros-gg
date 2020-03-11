<?php

namespace App\Manager\LeagueOfLegends\Riot;

use App\Entity\LeagueOfLegends\Player\Ranking;
use Exception;
use RiotAPI\LeagueAPI\Definitions\Region;
use RiotAPI\LeagueAPI\LeagueAPI;
use RiotAPI\LeagueAPI\Objects\CurrentGameParticipant;
use RiotAPI\LeagueAPI\Objects\LeagueEntryDto;
use RiotAPI\LeagueAPI\Objects\LeagueListDto;

class RiotLeagueManager
{
    /**
     * @var LeagueAPI
     */
    private $api;

    public $summoners;

    public function __construct(string $apiKey)
    {
        $this->api = new LeagueAPI([
            LeagueAPI::SET_KEY => $apiKey,
            LeagueAPI::SET_REGION => Region::EUROPE_WEST,
            LeagueAPI::SET_VERIFY_SSL => false,
        ]);
    }

    private function getSoloQ(array $leagues): LeagueEntryDto
    {
        $soloQ = array_filter($leagues, function ($league) {
            /* @var LeagueEntryDto $league */
            return $league->queueType && Ranking::QUEUE_TYPE_SOLO === $league->queueType;
        });

        if (!count($soloQ)) {
            return null;
        }

        return array_key_exists(0, $soloQ) ? $soloQ[0] : reset($soloQ);
    }

    public function getSoloQForId(string $id): ?LeagueEntryDto
    {
        return $this->getSoloQ($this->api->getLeagueEntriesForSummoner($id));
    }

    public function getMultipleId(array $participants): array
    {
        $this->summoners = [];
        foreach ($participants as $participant) {
            /* @var CurrentGameParticipant $participant */
            $this->api->nextAsync(
                function ($entryDto) use ($participant) {$this->summoners[$participant->summonerId] = $this->getSoloQ($entryDto); },
                function ($result) { throw new Exception($result); },
                'entries'
            )->getLeagueEntriesForSummoner($participant->summonerId);
        }
        $this->api->commitAsync('entries');

        return $this->summoners;
    }

    public function getChallengers(): LeagueListDto
    {
        return $this->api->getLeagueChallenger(Ranking::QUEUE_TYPE_SOLO);
    }
}
