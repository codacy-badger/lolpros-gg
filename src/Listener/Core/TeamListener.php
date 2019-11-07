<?php

namespace App\Listener\Core;

use App\Entity\Core\Team\Member;
use App\Entity\Core\Team\Team;
use App\Entity\LeagueOfLegends\Player\Player;
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
    private $identityIndexer;

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

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $teamIndexer, Indexer $identityIndexer, Indexer $ladderIndexer, Indexer $membersIndexer)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->teamIndexer = $teamIndexer;
        $this->identityIndexer = $identityIndexer;
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
        /* @var Member $member */
        foreach ($entity->getMembers() as $member) {
            $this->identityIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_IDENTITY, $member->getIdentity());
            $this->membersIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_MEMBER, $member);
            if ($member->getIdentity() instanceof Player) {
                $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $member->getIdentity());
            }
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
        /* @var Member $member */
        foreach ($entity->getMembers() as $member) {
            /* @var Member $member */
            $this->identityIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_IDENTITY, $member->getIdentity());
            $this->membersIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_MEMBER, $member);
            if ($member->getIdentity() instanceof Player) {
                $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $member->getIdentity());
            }
        }
        $this->adminLogManager->createLog(TeamEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }
}
