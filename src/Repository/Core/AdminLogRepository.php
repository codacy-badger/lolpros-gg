<?php

namespace App\Repository\Core;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AdminLogRepository extends EntityRepository
{
    public function getPaginated(int $page = 1, int $pageSize = 20, $type = null, $user = null): Paginator
    {
        $queryBuilder = $this->createQueryBuilder('admin_log')
            ->leftJoin('admin_log.user', 'user');

        if ($type) {
            $queryBuilder->andWhere('admin_log.type = :type')->setParameter('type', $type);
        }
        if ($user) {
            $queryBuilder->andWhere('user = :user')->setParameter('user', $user);
        }

        $queryBuilder
            ->orderBy('admin_log.createdAt', 'DESC')
            ->setFirstResult($pageSize * ($page - 1))
            ->setMaxResults($pageSize);

        return new Paginator($queryBuilder->getQuery());
    }
}
