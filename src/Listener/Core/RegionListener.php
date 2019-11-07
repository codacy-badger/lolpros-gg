<?php

namespace App\Listener\Core;

use App\Entity\Core\Region\Region;
use App\Entity\LeagueOfLegends\Player\Player;
use App\Event\Core\Region\RegionEvent;
use App\Indexer\Indexer;
use App\Manager\Core\Report\AdminLogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RegionListener implements EventSubscriberInterface
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
            RegionEvent::CREATED => 'onCreate',
            RegionEvent::UPDATED => 'onUpdate',
            RegionEvent::DELETED => 'onDelete',
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

    private function updateLinkedEntities(Region $region)
    {
        foreach ($region->getIdentities() as $identity) {
            $this->identityIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_IDENTITY, $identity);
            if ($identity instanceof Player) {
                $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $identity);
            }
        }
        foreach ($region->getTeams() as $team) {
            $this->teamIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_TEAM, $team);
        }
    }

    public function onCreate(RegionEvent $event)
    {
        $entity = $event->getRegion();

        if (!$entity instanceof Region) {
            return;
        }
        $this->adminLogManager->createLog(RegionEvent::CREATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onUpdate(RegionEvent $event)
    {
        $entity = $event->getRegion();

        if (!$entity instanceof Region) {
            return;
        }

        $this->updateLinkedEntities($entity);
        $this->adminLogManager->createLog(RegionEvent::UPDATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onDelete(RegionEvent $event)
    {
        $entity = $event->getRegion();

        if (!$entity instanceof Region) {
            return;
        }

        $this->updateLinkedEntities($entity);
        $this->adminLogManager->createLog(RegionEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }
}
