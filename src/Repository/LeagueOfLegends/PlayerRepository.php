<?php

namespace App\Repository\LeagueOfLegends;

use Doctrine\ORM\EntityRepository;

/**
 * PlayerRepository.
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class PlayerRepository extends EntityRepository
{
    public function search(string $name): array
    {
        return $this->createQueryBuilder('player')
            ->andWhere('player.name LIKE :name')
            ->setParameter('name', '%'.$name.'%')
            ->getQuery()
            ->getResult();
    }

    public function getPlayersRanked($playerUuid, $position = null, $country = null): int
    {
        $sql = <<<SQL
SELECT player.name, player.uuid, RANK() OVER (ORDER BY player.score DESC) AS rank FROM player__player player
SQL;
        if ($position && !$country) {
            $sql .= " WHERE player.position = '{$position}'";
        } elseif ($country && !$position) {
            $sql .= " WHERE player.country = '{$country}'";
        } elseif ($country && $position) {
            $sql .= " WHERE player.position = '{$position}' AND player.country = '{$country}'";
        }

        $query = $this->getEntityManager()->getConnection()->prepare($sql);
        $query->execute();

        $results = $query->fetchAll();

        return $results[array_search($playerUuid, array_column($results, 'uuid'))]['rank'];
    }
}
