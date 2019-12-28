<?php

namespace App\Listener\Core;

use App\Entity\Core\Player\Player;
use App\Event\Core\Player\PlayerEvent;
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
    private $playerIndexer;

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CREATED => 'onCreate',
            PlayerEvent::UPDATED => 'onUpdate',
            PlayerEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $playerIndexer)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->playerIndexer = $playerIndexer;
    }

    public function onCreate(PlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Player) {
            return;
        }

        $this->playerIndexer->addOne(Indexer::INDEX_TYPE_PLAYER, $entity);
        $this->adminLogManager->createLog(PlayerEvent::CREATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onUpdate(PlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Player) {
            return;
        }

        $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PLAYER, $entity);
        $this->adminLogManager->createLog(PlayerEvent::UPDATED, $entity->getUuidAsString(), $entity->getName());
    }

    public function onDelete(PlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof Player) {
            return;
        }

        $this->playerIndexer->deleteOne(Indexer::INDEX_TYPE_PLAYER, $entity->getUuidAsString());
        $this->adminLogManager->createLog(PlayerEvent::DELETED, $entity->getUuidAsString(), $entity->getName());
    }
}
