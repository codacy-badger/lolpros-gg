<?php

namespace App\Repository\Core;

use Doctrine\ORM\EntityRepository;

class IdentityRepository extends EntityRepository
{
    public function getCountries()
    {
        $query = $this->getEntityManager()->getConnection()->prepare('select country from player__player GROUP BY country');
        $query->execute();

        $array = $query->fetchAll();

        $flatten = [];
        array_walk_recursive($array, function ($value) use (&$flatten) {
            $flatten[] = $value;
        });

        return $flatten;
    }
}
