<?php

namespace App\Listener\LeagueOfLegends;

use App\Entity\LeagueOfLegends\LeaguePlayer;
use App\Event\LeagueOfLegends\LeaguePlayerEvent;
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
            LeaguePlayerEvent::CREATED => 'onCreate',
            LeaguePlayerEvent::UPDATED => 'onUpdate',
            LeaguePlayerEvent::DELETED => 'onDelete',
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

    public function onCreate(LeaguePlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof LeaguePlayer) {
            return;
        }

        $player = $entity->getPlayer();
        $this->playerIndexer->addOne(Indexer::INDEX_TYPE_PLAYER, $player);
        $this->ladderIndexer->addOne(Indexer::INDEX_TYPE_LADDER, $entity);

        $this->adminLogManager->createLog(LeaguePlayerEvent::CREATED, $player->getUuidAsString(), $player->getName());
    }

    public function onUpdate(LeaguePlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof LeaguePlayer) {
            return;
        }

        $player = $entity->getPlayer();
        $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PLAYER, $player);
        $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $entity);
        $this->membersIndexer->updateMultiple(Indexer::INDEX_TYPE_MEMBER, $this->memberRepository->getMembersUuidsFromPlayer($player));

        $this->adminLogManager->createLog(LeaguePlayerEvent::UPDATED, $player->getUuidAsString(), $player->getName());
    }

    public function onDelete(LeaguePlayerEvent $event)
    {
        $entity = $event->getPlayer();

        if (!$entity instanceof LeaguePlayer) {
            return;
        }

        $player = $entity->getPlayer();
        $this->playerIndexer->deleteOne(Indexer::INDEX_TYPE_PLAYER, $player->getUuidAsString());
        $this->ladderIndexer->deleteOne(Indexer::INDEX_TYPE_LADDER, $player->getUuidAsString());
        $this->membersIndexer->deleteMultiple(Indexer::INDEX_TYPE_MEMBER, $this->memberRepository->getMembersUuidsFromPlayer($player));

        $this->adminLogManager->createLog(LeaguePlayerEvent::DELETED, $player->getUuidAsString(), $player->getName());
    }
}
