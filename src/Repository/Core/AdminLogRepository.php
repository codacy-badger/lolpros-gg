<?php

namespace App\Repository\Core;

use App\Entity\Core\Report\AdminLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AdminLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminLog::class);
    }

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
