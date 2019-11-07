<?php

namespace App\Listener\LeagueOfLegends;

use App\Entity\Core\Team\Member;
use App\Entity\LeagueOfLegends\Player\Player;
use App\Event\LeagueOfLegends\Player\PlayerEvent;
use App\Indexer\Indexer;
use App\Manager\Core\Report\AdminLogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerListener implements EventSubscriberInterface
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
    private $ladderIndexer;

    /**
     * @var Indexer
     */
    private $teamIndexer;

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CREATED => 'onCreate',
            PlayerEvent::UPDATED => 'onUpdate',
            PlayerEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $identityIndexer, Indexer $ladderIndexer, Indexer $teamIndexer)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->identityIndexer = $identityIndexer;
        $this->ladderIndexer = $ladderIndexer;
        $this->teamIndexer = $teamIndexer;
    }

    public function onCreate(PlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Player) {
            return;
        }

        $this->identityIndexer->addOne(Indexer::INDEX_TYPE_IDENTITY, $entity);
        $this->ladderIndexer->addOne(Indexer::INDEX_TYPE_LADDER, $entity);
        $this->adminLogManager->createLog(PlayerEvent::CREATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onUpdate(PlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Player) {
            return;
        }

        $this->identityIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_IDENTITY, $entity);
        $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $entity);
        foreach ($entity->getMemberships() as $membership) {
            /* @var Member $membership */
            $this->teamIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_TEAM, $membership->getTeam());
        }
        $this->adminLogManager->createLog(PlayerEvent::UPDATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onDelete(PlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Player) {
            return;
        }

        $this->identityIndexer->deleteOne(Indexer::INDEX_TYPE_IDENTITY, $entity->getUuidAsString());
        $this->ladderIndexer->deleteOne(Indexer::INDEX_TYPE_LADDER, $entity->getUuidAsString());
        /* @var Member $membership */
        foreach ($entity->getMemberships() as $membership) {
            /* @var Member $membership */
            $this->teamIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_TEAM, $membership->getTeam());
        }
        $this->adminLogManager->createLog(PlayerEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }
}
