<?php

namespace App\Listener\Core;

use App\Entity\Core\Team\Member;
use App\Entity\LeagueOfLegends\Player\Player;
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
            MemberEvent::CREATED => 'onCreate',
            MemberEvent::UPDATED => 'onUpdate',
            MemberEvent::DELETED => 'onDelete',
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

    public function onCreate(MemberEvent $event)
    {
        $entity = $event->getMember();

        if (!$entity instanceof Member) {
            return;
        }

        $this->membersIndexer->addOne(Indexer::INDEX_TYPE_MEMBER, $entity);
        $this->updateRelations($entity);
        $this->adminLogManager->createLog(MemberEvent::CREATED, $entity->getTeam()->getUuidAsString(), $entity->getTeam()->getName(), $entity->getIdentity()->getUuidAsString(), $entity->getIdentity()->getName());
    }

    public function onUpdate(MemberEvent $event)
    {
        $entity = $event->getMember();

        if (!$entity instanceof Member) {
            return;
        }
        $this->membersIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_MEMBER, $entity);
        $this->updateRelations($entity);
        $this->adminLogManager->createLog(MemberEvent::UPDATED, $entity->getTeam()->getUuidAsString(), $entity->getTeam()->getName(), $entity->getIdentity()->getUuidAsString(), $entity->getIdentity()->getName());
    }

    public function onDelete(MemberEvent $event)
    {
        $entity = $event->getMember();

        if (!$entity instanceof Member) {
            return;
        }

        $this->membersIndexer->deleteOne(Indexer::INDEX_TYPE_MEMBER, $entity->getUuidAsString());
        $this->updateRelations($entity);
        $this->adminLogManager->createLog(MemberEvent::DELETED, $entity->getTeam()->getUuidAsString(), $entity->getTeam()->getName(), $entity->getIdentity()->getUuidAsString(), $entity->getIdentity()->getName());
    }

    private function updateRelations(Member $entity)
    {
        $this->teamIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_TEAM, $entity->getTeam());
        $this->identityIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_IDENTITY, $entity->getIdentity());
        if ($entity->getIdentity() instanceof Player) {
            $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $entity->getIdentity());
        }
    }
}
