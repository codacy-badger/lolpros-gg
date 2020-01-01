<?php

namespace App\Repository\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * PlayerRepository.
 *
 * This class was generated by the PhpStorm "Php Annotations" Plugin. Add your own custom
 * repository methods below.
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function getPlayersRankings($playerUuid, $position = null, $country = null): int
    {
        $sql = <<<SQL
SELECT player.name, player.uuid, RANK() OVER (ORDER BY player.score DESC) AS ranking FROM player__player player
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

        return $results[array_search($playerUuid, array_column($results, 'uuid'))]['ranking'];
    }

    public function getPaginated(int $page = 1, int $pageSize = 20): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder('players')
                ->addSelect('memberships')
                ->leftJoin('players.memberships', 'memberships')
                ->orderBy('players.name', 'ASC')
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getQuery()
        );
    }

    public function searchPaginated(string $query, int $page = 1, int $pageSize = 20): Paginator
    {
        return new Paginator(
            $this->createQueryBuilder('players')
                ->leftJoin('players.memberships', 'memberships')
                ->andWhere('players.name LIKE :name')
                ->setParameter('name', '%'.$query.'%')
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize)
                ->getQuery()
        );
    }
}
