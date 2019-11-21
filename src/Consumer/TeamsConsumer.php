<?php

namespace App\Consumer;

use App\Entity\Core\Team\Team;
use App\Indexer\Indexer;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;

class TeamsConsumer implements ConsumerInterface
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
    private $teamIndexer;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, Indexer $teamIndexer)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->teamIndexer = $teamIndexer;
    }

    public function execute(AMQPMessage $msg): bool
    {
        $this->logger->notice('[TeamsConsumers] Starting update');
        try {
            $teams = $this->entityManager->getRepository(Team::class)->getTeamsUuids();
            $this->teamIndexer->updateMultiple(Indexer::INDEX_TYPE_TEAM, $teams);
        } catch (Exception $e) {
            $this->logger->critical(sprintf('[TeamsConsumers] An error occured %s', $e->getMessage()));

            return false;
        }

        $this->logger->notice('[TeamsConsumers] Update finished');

        return true;
    }
}
