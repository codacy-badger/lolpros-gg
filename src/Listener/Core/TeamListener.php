<?php

namespace App\Listener\Core;

use App\Entity\Core\Team\Member;
use App\Entity\Core\Team\Team;
use App\Event\Core\Team\TeamEvent;
use App\Indexer\Indexer;
use App\Manager\Core\Report\AdminLogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TeamListener implements EventSubscriberInterface
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
    private $teamIndexer;

    /**
     * @var Indexer
     */
    private $playerIndexer;

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
            TeamEvent::CREATED => 'onCreate',
            TeamEvent::UPDATED => 'onUpdate',
            TeamEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $teamIndexer, Indexer $playerIndexer, Indexer $ladderIndexer, Indexer $membersIndexer)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->teamIndexer = $teamIndexer;
        $this->playerIndexer = $playerIndexer;
        $this->ladderIndexer = $ladderIndexer;
        $this->membersIndexer = $membersIndexer;
    }

    public function onCreate(TeamEvent $event)
    {
        $entity = $event->getTeam();

        if (!$entity instanceof Team) {
            return;
        }

        $this->teamIndexer->addOne(Indexer::INDEX_TYPE_TEAM, $entity);
        $this->adminLogManager->createLog(TeamEvent::CREATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onUpdate(TeamEvent $event)
    {
        $entity = $event->getTeam();

        if (!$entity instanceof Team) {
            return;
        }

        $this->teamIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_TEAM, $entity);
        foreach ($entity->getMembers() as $member) {
            /* @var Member $member */
            $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PLAYER, $member->getPlayer());
            $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $member->getPlayer());
            $this->membersIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_MEMBER, $member);
        }
        $this->adminLogManager->createLog(TeamEvent::UPDATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onDelete(TeamEvent $event)
    {
        $entity = $event->getTeam();

        if (!$entity instanceof Team) {
            return;
        }

        $this->teamIndexer->deleteOne(Indexer::INDEX_TYPE_TEAM, $entity->getUuidAsString());
        foreach ($entity->getMembers() as $member) {
            /* @var Member $member */
            $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PLAYER, $member->getPlayer());
            $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $member->getPlayer());
            $this->membersIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_MEMBER, $member);
        }
        $this->adminLogManager->createLog(TeamEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }
}
