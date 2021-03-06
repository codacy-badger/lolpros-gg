<?php

namespace App\Listener\Core;

use App\Entity\Core\Team\Team;
use App\Event\Core\Team\TeamEvent;
use App\Indexer\Indexer;
use App\Manager\Core\Report\AdminLogManager;
use App\Repository\Core\MemberRepository;
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
    private $ladderIndexer;

    /**
     * @var Indexer
     */
    private $membersIndexer;

    /**
     * @var MemberRepository
     */
    protected $membersRepository;

    public static function getSubscribedEvents()
    {
        return [
            TeamEvent::CREATED => 'onCreate',
            TeamEvent::UPDATED => 'onUpdate',
            TeamEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $teamIndexer, Indexer $ladderIndexer, Indexer $membersIndexer, MemberRepository $membersRepository)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->teamIndexer = $teamIndexer;
        $this->ladderIndexer = $ladderIndexer;
        $this->membersIndexer = $membersIndexer;
        $this->membersRepository = $membersRepository;
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
        $this->ladderIndexer->updateMultiple(Indexer::INDEX_TYPE_LADDER, $this->membersRepository->getPlayersUuidsFromTeam($entity));
        $this->membersIndexer->updateMultiple(Indexer::INDEX_TYPE_MEMBER, $this->membersRepository->getMembersUuidsFromTeam($entity));

        $this->adminLogManager->createLog(TeamEvent::UPDATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onDelete(TeamEvent $event)
    {
        $entity = $event->getTeam();

        if (!$entity instanceof Team) {
            return;
        }

        $this->teamIndexer->deleteOne(Indexer::INDEX_TYPE_TEAM, $entity->getUuidAsString());
        $this->ladderIndexer->updateMultiple(Indexer::INDEX_TYPE_LADDER, $this->membersRepository->getPlayersUuidsFromTeam($entity));
        $this->membersIndexer->updateMultiple(Indexer::INDEX_TYPE_MEMBER, $this->membersRepository->getMembersUuidsFromTeam($entity));

        $this->adminLogManager->createLog(TeamEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }
}
