<?php

namespace App\Listener\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player\Ranking;
use App\Event\LeagueOfLegends\Player\RankingEvent;
use App\Indexer\Indexer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RankingListener implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Indexer
     */
    private $identityIndexer;

    /**
     * @var Indexer
     */
    private $ladderIndexer;

    public static function getSubscribedEvents()
    {
        return [
            RankingEvent::CREATED => 'onCreate',
        ];
    }

    public function __construct(LoggerInterface $logger, Indexer $identityIndexer, Indexer $ladderIndexer)
    {
        $this->logger = $logger;
        $this->identityIndexer = $identityIndexer;
        $this->ladderIndexer = $ladderIndexer;
    }

    public function onCreate(RankingEvent $event)
    {
        $this->logger->debug('[RankingListener::onCreate]');
        $entity = $event->getRanking();

        if (!$entity instanceof Ranking) {
            return;
        }

        $this->identityIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_IDENTITY, $entity->getOwner()->getPlayer());
        $this->ladderIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_LADDER, $entity->getOwner()->getPlayer());
    }
}
