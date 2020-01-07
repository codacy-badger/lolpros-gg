<?php

namespace App\Listener\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player;
use App\Event\LeagueOfLegends\PlayerEvent;
use App\Indexer\Indexer;
use App\Manager\Report\AdminLogManager;
use App\Repository\MemberRepository;
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
    private $memberRepository;

    public static function getSubscribedEvents()
    {
        return [
            PlayerEvent::CREATED => 'onCreate',
            PlayerEvent::UPDATED => 'onUpdate',
            PlayerEvent::DELETED => 'onDelete',
        ];
    }

    public function __construct(LoggerInterface $logger, AdminLogManager $adminLogManager, Indexer $playerIndexer, Indexer $ladderIndexer, Indexer $membersIndexer, MemberRepository $memberRepository)
    {
        $this->logger = $logger;
        $this->adminLogManager = $adminLogManager;
        $this->playerIndexer = $playerIndexer;
        $this->ladderIndexer = $ladderIndexer;
        $this->membersIndexer = $membersIndexer;
        $this->memberRepository = $memberRepository;
    }

    public function onCreate(PlayerEvent $event)
    {
        $entity = $event->getProfile();

        if (!$entity instanceof Player) {
            return;
        }

        $player = $entity->getLeaguePlayer();
        $this->playerIndexer->addOne(Indexer::INDEX_TYPE_PLAYER, $player);
        $this->ladderIndexer->addOne(Indexer::INDEX_TYPE_LADDER, $entity);

        $this->adminLogManager->createLog(PlayerEvent::CREATED, $player->getUuidAsString(), $entity->getName());
    }

    public function onUpdate(PlayerEvent $event)
    {
        $entity = $event->getProfile();

        if (!$entity instanceof Player) {
            return;
        }

        $player = $entity->getLeaguePlayer();
        $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PLAYER, $player);
        $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $entity);
        $this->membersIndexer->updateMultiple(Indexer::INDEX_TYPE_MEMBER, $this->memberRepository->getMembersUuidsFromPlayer($entity));

        $this->adminLogManager->createLog(PlayerEvent::UPDATED, $player->getUuidAsString(), $entity->getName());
    }

    public function onDelete(PlayerEvent $event)
    {
        $entity = $event->getProfile();

        if (!$entity instanceof Player) {
            return;
        }

        $player = $entity->getLeaguePlayer();
        $this->playerIndexer->deleteOne(Indexer::INDEX_TYPE_PLAYER, $player->getUuidAsString());
        $this->ladderIndexer->deleteOne(Indexer::INDEX_TYPE_LADDER, $player->getUuidAsString());
        $this->membersIndexer->deleteMultiple(Indexer::INDEX_TYPE_MEMBER, $this->memberRepository->getMembersUuidsFromPlayer($entity));

        $this->adminLogManager->createLog(PlayerEvent::DELETED, $player->getUuidAsString(), $entity->getName());
    }
}
