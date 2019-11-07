<?php

namespace App\Consumer;

use App\Entity\Core\Team\Team;
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
    private $identityIndexer;

    /**
     * @var Indexer
     */
    private $ladderIndexer;

    /**
     * @var Indexer
     */
    private $teamIndexer;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, Indexer $identityIndexer, Indexer $ladderIndexer, Indexer $teamIndexer)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->identityIndexer = $identityIndexer;
        $this->ladderIndexer = $ladderIndexer;
        $this->teamIndexer = $teamIndexer;
    }

    public function execute(AMQPMessage $msg): bool
    {
        $this->logger->notice('[PlayersConsumer] Starting update');
        try {
            $ids = json_decode($msg->body);
            $this->identityIndexer->updateMultiple(Indexer::INDEX_TYPE_IDENTITY, $ids);
            $this->ladderIndexer->updateMultiple(Indexer::INDEX_TYPE_LADDER, $ids);

            $teams = $this->entityManager->getRepository(Team::class)->getTeamsUuids();
            $this->teamIndexer->updateMultiple(Indexer::INDEX_TYPE_TEAM, $teams);
        } catch (Exception $e) {
            $this->logger->critical(sprintf('[PlayersConsumer] An error occured %s', $e->getMessage()));

            return false;
        }

        $this->logger->notice('[PlayersConsumer] Update finished');

        return true;
    }
}
