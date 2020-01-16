<?php

namespace App\Consumer;

use App\Entity\LeagueOfLegends\SummonerName;
use App\Indexer\Indexer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SummonerNamesConsumer implements ConsumerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Indexer
     */
    private $summonerNameIndexer;

    /**
     * @var Indexer
     */
    private $playerIndexer;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher, Indexer $summonerNameIndexer, Indexer $playerIndexer)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        $this->summonerNameIndexer = $summonerNameIndexer;
        $this->playerIndexer = $playerIndexer;
    }

    public function execute(AMQPMessage $msg)
    {
        $summoner = $this->entityManager->getRepository(SummonerName::class)->findOneBy(['id' => $msg->body]);

        if (!$summoner instanceof SummonerName) {
            $this->logger->error(sprintf('[SummonerNamesConsumer] Could\'t find a summoner name with the id %s', $msg->body));

            return true;
        }

        try {
            $this->summonerNameIndexer->addOne(Indexer::INDEX_TYPE_SUMMONER_NAME, $summoner);
            $this->playerIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_PROFILE, $summoner->getOwner()->getPlayer());
            if ($previous = $summoner->getPrevious()) {
                $this->summonerNameIndexer->addOrUpdateOne(Indexer::INDEX_TYPE_SUMMONER_NAME, $previous);
            }
        } catch (Exception $e) {
            $this->logger->critical(sprintf('[SummonerNamesConsumer] An error occured %s', $e->getMessage()));

            return false;
        }

        $this->logger->notice(sprintf('[SummonerNamesConsumer] Handled summoner %s (%s)', $msg->body, $summoner->getName()));

        return true;
    }
}
