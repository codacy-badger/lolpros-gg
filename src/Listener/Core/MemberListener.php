<?php

namespace App\Listener\Core;

use App\Entity\Core\Team\Member;
use App\Event\Core\Team\MemberEvent;
use App\Indexer\Indexer;
use App\Manager\Core\Report\AdminLogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MemberListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AdminLogManager
     */
    private $adminLogManager;

    /**
     * @var Indexer
     */
    private $ladderIndexer;

    /**
     * @var Indexer
     */
    private $membersIndexer;

    public static function getSubscribedEvents()
    {
        return [
            MemberEvent::CREATED => 'onCreate',
            MemberEvent::UPDATED => 'onUpdate',
            MemberEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $ladderIndexer, Indexer $membersIndexer)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->ladderIndexer = $ladderIndexer;
        $this->membersIndexer = $membersIndexer;
    }

    public function onCreate(MemberEvent $event)
    {
        $entity = $event->getMember();

        if (!$entity instanceof Member) {
            return;
        }

        $this->membersIndexer->addOne(Indexer::INDEX_TYPE_MEMBER, $entity);
        $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $entity->getPlayer());

        $this->adminLogManager->createLog(MemberEvent::CREATED, $entity->getTeam()->getUuidAsString(), $entity->getTeam()->getName(), $entity->getPlayer()->getUuidAsString(), $entity->getPlayer()->getName());
    }

    public function onUpdate(MemberEvent $event)
    {
        $entity = $event->getMember();

        if (!$entity instanceof Member) {
            return;
        }

        $this->membersIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_MEMBER, $entity);
        $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $entity->getPlayer());

        $this->adminLogManager->createLog(MemberEvent::UPDATED, $entity->getTeam()->getUuidAsString(), $entity->getTeam()->getName(), $entity->getPlayer()->getUuidAsString(), $entity->getPlayer()->getName());
    }

    public function onDelete(MemberEvent $event)
    {
        $entity = $event->getMember();

        if (!$entity instanceof Member) {
            return;
        }

        $this->membersIndexer->deleteOne(Indexer::INDEX_TYPE_MEMBER, $entity->getUuidAsString());
        $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $entity->getPlayer());

        $this->adminLogManager->createLog(MemberEvent::DELETED, $entity->getTeam()->getUuidAsString(), $entity->getTeam()->getName(), $entity->getPlayer()->getUuidAsString(), $entity->getPlayer()->getName());
    }
}
