<?php

namespace App\Consumer;

use App\Indexer\Indexer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class PlayersConsumer implements ConsumerInterface
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
     * @var Indexer
     */
    private $playerIndexer;

    /**
     * @var Indexer
     */
    private $ladderIndexer;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, Indexer $playerIndexer, Indexer $ladderIndexer)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->playerIndexer = $playerIndexer;
        $this->ladderIndexer = $ladderIndexer;
    }

    public function execute(AMQPMessage $msg): bool
    {
        $this->logger->notice('[PlayersConsumer] message received');
        try {
            $ids = json_decode($msg->body);
            $this->logger->notice(sprintf('[PlayersConsumer] Starting update %s players', count($ids)));

            $this->playerIndexer->updateMultiple(Indexer::INDEX_TYPE_PROFILE, $ids);
            $this->ladderIndexer->updateMultiple(Indexer::INDEX_TYPE_LADDER, $ids);
        } catch (Exception $e) {
            $this->logger->critical(sprintf('[PlayersConsumer] An error occured %s', $e->getMessage()));

            return false;
        }

        $this->logger->notice('[PlayersConsumer] Update finished');

        return true;
    }
}
