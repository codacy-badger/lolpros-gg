<?php

namespace App\Repository\Core;

use App\Entity\Core\Player\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function getCountries()
    {
        $query = $this->getEntityManager()->getConnection()->prepare('SELECT country FROM player__player GROUP BY country');
        $query->execute();

        $array = $query->fetchAll();

        $flatten = [];
        array_walk_recursive($array, function ($value) use (&$flatten) {
            $flatten[] = $value;
        });

        return $flatten;
    }
}
