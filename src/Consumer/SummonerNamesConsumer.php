<?php

namespace App\Consumer;

use App\Entity\LeagueOfLegends\Player\SummonerName;
use App\Event\LeagueOfLegends\Player\SummonerNameEvent;
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

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function execute(AMQPMessage $msg)
    {
        $summoner = $this->entityManager->getRepository(SummonerName::class)->findOneBy(['id' => $msg->body]);

        if (!$summoner instanceof SummonerName) {
            $this->logger->error(sprintf('[SummonerNamesConsumer] Could\'t find a summoner name with the id %s', $msg->body));

            return true;
        }

        try {
            $this->eventDispatcher->dispatch(new SummonerNameEvent($summoner), SummonerNameEvent::CREATED);
        } catch (Exception $e) {
            $this->logger->critical(sprintf('[SummonerNamesConsumer] An error occured %s', $e->getMessage()));

            return false;
        }

        $this->logger->notice(sprintf('[SummonerNamesConsumer] Handled summoner %s (%s)', $msg->body, $summoner->getName()));

        return true;
    }
}
