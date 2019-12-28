<?php

namespace App\Manager\Core\Report;

use App\Entity\Core\Report\AdminLog;
use App\Manager\DefaultManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdminLogManager extends DefaultManager
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher, Security $security)
    {
        parent::__construct($entityManager, $logger, $eventDispatcher);
        $this->security = $security;
    }

    public function createLog(string $type, string $entityUuid = '', string $entityName = '', string $linkedUuid = null, string $linkedName = null): ?AdminLog
    {
        try {
            $log = new AdminLog();
            $log->setUser($this->security->getUser());
            $log->setType($type);
            $log->setEntityUuid($entityUuid);
            $log->setEntityName($entityName);
            $log->setLinkedUuid($linkedUuid);
            $log->setLinkedName($linkedName);

            $this->entityManager->persist($log);
            $this->entityManager->flush();

            return $log;
        } catch (Exception $e) {
            $this->logger->error('[AdminLogManager] Could not create log because of {reason}', [
                'reason' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
