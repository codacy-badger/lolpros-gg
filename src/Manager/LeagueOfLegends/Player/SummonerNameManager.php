<?php

namespace App\Manager\LeagueOfLegends\Player;

use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Entity\LeagueOfLegends\Player\SummonerName;
use App\Event\LeagueOfLegends\Player\SummonerNameEvent;
use App\Manager\DefaultManager;
use App\Manager\LeagueOfLegends\Riot\RiotSummonerManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RiotAPI\LeagueAPI\Objects\SummonerDto;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class SummonerNameManager extends DefaultManager
{
    /**
     * @var RiotSummonerManager
     */
    private $riotSummonerManager;

    /**
     * SummonerNamesManager constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, EventDispatcherInterface $eventDispatcher, RiotSummonerManager $riotSummonerManager)
    {
        parent::__construct($entityManager, $logger, $eventDispatcher);
        $this->riotSummonerManager = $riotSummonerManager;
    }

    public static function createFromSummoner(SummonerDto $summoner): SummonerName
    {
        $name = new SummonerName();
        $name->setName($summoner->name);

        return $name;
    }

    public function updateSummonerName(RiotAccount $riotAccount)
    {
        // Refresh Summoner name
        $current = $riotAccount->getCurrentSummonerName();
        $summoner = $this->riotSummonerManager->getForId($riotAccount->getEncryptedRiotId());

        if ($summoner->name !== $current->getName()) {
            $name = self::createFromSummoner($summoner);
            $current->setCurrent(false);
            $current->setNext($name);
            $name->setCurrent(true);
            $name->setOwner($riotAccount);
            $name->setPrevious($current);
            $riotAccount->addSummonerName($name);

            $this->entityManager->persist($name);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new SummonerNameEvent($name), SummonerNameEvent::CREATED);
        }
    }
}
