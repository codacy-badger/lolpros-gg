<?php

namespace App\Repository\LeagueOfLegends;

use App\Entity\LeagueOfLegends\LeaguePlayer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class LeaguePlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LeaguePlayer::class);
    }

    public function getPlayersRankings($playerUuid, $position = null, $country = null): int
    {
        $sql = <<<SQL
SELECT profile.name, profile.uuid, RANK() OVER (ORDER BY player.score DESC) AS ranking 
FROM profile__league_player AS player 
    JOIN profile__profile AS profile ON profile.id=player.profile_id
SQL;
        if ($position && !$country) {
            $sql .= " WHERE profile.position = '{$position}'";
        } elseif ($country && !$position) {
            $sql .= " WHERE player.country = '{$country}'";
        } elseif ($country && $position) {
            $sql .= " WHERE profile.position = '{$position}' AND player.country = '{$country}'";
        }

        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->execute();

        $results = $query->fetchAll();

        return $results[array_search($playerUuid, array_column($results, 'uuid'))]['ranking'];
    }
}
