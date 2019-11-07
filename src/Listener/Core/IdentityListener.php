<?php

namespace App\Listener\Core;

use App\Entity\Core\Identity\Identity;
use App\Entity\Core\Team\Member;
use App\Event\Core\Identity\IdentityEvent;
use App\Indexer\Indexer;
use App\Manager\Core\Report\AdminLogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class IdentityListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var AdminLogManager
     */
    protected $adminLogManager;

    /**
     * @var Indexer
     */
    private $identityIndexer;

    /**
     * @var Indexer
     */
    private $teamIndexer;

    public static function getSubscribedEvents()
    {
        return [
            IdentityEvent::CREATED => 'onCreate',
            IdentityEvent::UPDATED => 'onUpdate',
            IdentityEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $identityIndexer, Indexer $teamIndexer)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->identityIndexer = $identityIndexer;
        $this->teamIndexer = $teamIndexer;
    }

    public function onCreate(IdentityEvent $event)
    {
        $entity = $event->getIdentity();

        if (!$entity instanceof Identity) {
            return;
        }

        $this->identityIndexer->addOne(Indexer::INDEX_TYPE_IDENTITY, $entity);
        $this->adminLogManager->createLog(IdentityEvent::CREATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onUpdate(IdentityEvent $event)
    {
        $entity = $event->getIdentity();

        if (!$entity instanceof Identity) {
            return;
        }

        $this->identityIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_IDENTITY, $entity);
        foreach ($entity->getMemberships() as $membership) {
            /* @var Member $membership */
            $this->teamIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_TEAM, $membership->getTeam());
        }
        $this->adminLogManager->createLog(IdentityEvent::UPDATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onDelete(IdentityEvent $event)
    {
        $entity = $event->getIdentity();

        if (!$entity instanceof Identity) {
            return;
        }

        $this->identityIndexer->deleteOne(Indexer::INDEX_TYPE_IDENTITY, $entity->getUuidAsString());
        foreach ($entity->getMemberships() as $membership) {
            /* @var Member $membership */
            $this->teamIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_TEAM, $membership->getTeam());
        }
        $this->adminLogManager->createLog(IdentityEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }
}
