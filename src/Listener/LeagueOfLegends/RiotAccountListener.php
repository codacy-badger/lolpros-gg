<?php

namespace App\Listener\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player\Player;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Event\LeagueOfLegends\Player\RiotAccountEvent;
use App\Indexer\Indexer;
use App\Manager\Core\Report\AdminLogManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RiotAccountListener implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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
    private $playerIndexer;

    /**
     * @var Indexer
     */
    private $ladderIndexer;

    /**
     * @var Indexer
     */
    private $summonerNameIndexer;

    public static function getSubscribedEvents()
    {
        return [
            RiotAccountEvent::CREATED => 'onCreate',
            RiotAccountEvent::UPDATED => 'onUpdate',
            RiotAccountEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $playerIndexer, Indexer $ladderIndexer, Indexer $summonerNameIndexer)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->playerIndexer = $playerIndexer;
        $this->ladderIndexer = $ladderIndexer;
        $this->summonerNameIndexer = $summonerNameIndexer;
    }

    private function updateLinkedPlayer(Player $player)
    {
        $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PLAYER, $player);
        $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $player);
        $player->setScore($player->getScore());
        $this->entityManager->flush($player);
    }

    public function onCreate(RiotAccountEvent $event)
    {
        $entity = $event->getRiotAccount();

        if (!$entity instanceof RiotAccount) {
            return;
        }

        $this->updateLinkedPlayer($entity->getPlayer());
        $this->adminLogManager->createLog(RiotAccountEvent::CREATED, $entity->getUuidAsString(), $entity->getSummonerName());
    }

    public function onUpdate(RiotAccountEvent $event)
    {
        $entity = $event->getRiotAccount();

        if (!$entity instanceof RiotAccount) {
            return;
        }

        $this->updateLinkedPlayer($entity->getPlayer());
    }

    public function onDelete(RiotAccountEvent $event)
    {
        $entity = $event->getRiotAccount();

        if (!$entity instanceof RiotAccount) {
            return;
        }

        $this->updateLinkedPlayer($entity->getPlayer());
        $this->adminLogManager->createLog(RiotAccountEvent::DELETED, $entity->getUuidAsString());
    }
}
