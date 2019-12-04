<?php

namespace App\Listener\Core;

use App\Entity\Core\Medal\AMedal;
use App\Entity\Core\Medal\PlayerMedal;
use App\Entity\LeagueOfLegends\Medal\PlayerMedal as LoLPlayerMedal;
use App\Entity\LeagueOfLegends\Medal\RiotAccountMedal;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Event\Core\Medal\MedalEvent;
use App\Indexer\Indexer;
use App\Manager\Core\Report\AdminLogManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MedalListener implements EventSubscriberInterface
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
    private $playerIndexer;

    /**
     * @var Indexer
     */
    private $ladderIndexer;

    public static function getSubscribedEvents()
    {
        return [
            MedalEvent::CREATED => 'onCreate',
            MedalEvent::UPDATED => 'onUpdate',
            MedalEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $playerIndexer, Indexer $ladderIndexer)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->playerIndexer = $playerIndexer;
        $this->ladderIndexer = $ladderIndexer;
    }

    public function onCreate(MedalEvent $event)
    {
        $entity = $event->getMedal();

        if (!$entity instanceof AMedal) {
            return;
        }
        $this->adminLogManager->createLog(MedalEvent::CREATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onUpdate(MedalEvent $event)
    {
        $entity = $event->getMedal();

        if (!$entity instanceof AMedal) {
            return;
        }

        $this->updateLinkedEntities($entity);
        $this->adminLogManager->createLog(MedalEvent::UPDATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onDelete(MedalEvent $event)
    {
        $entity = $event->getMedal();

        if (!$entity instanceof AMedal) {
            return;
        }

        $this->updateLinkedEntities($entity);
        $this->adminLogManager->createLog(MedalEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }

    private function updateLinkedEntities(AMedal $medal): void
    {
        switch (true) {
            case $medal instanceof PlayerMedal:
            case $medal instanceof LoLPlayerMedal:
                foreach ($medal->getPlayers() as $player) {
                    $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PLAYER, $player);
                    $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $player);
                }
                break;
            case $medal instanceof RiotAccountMedal:
                foreach ($medal->getAccounts() as $account) {
                    /* @var RiotAccount $account */
                    $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PLAYER, $account->getPlayer());
                    $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $account->getPlayer());
                }
                break;
            default:
                $this->logger->critical('[MedalListener::onUpdate] Invalid entity');

                return;
        }
    }
}
