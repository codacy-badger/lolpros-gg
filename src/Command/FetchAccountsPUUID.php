<?php

namespace App\Command;

use App\Entity\LeagueOfLegends\RiotAccount;
use App\Manager\LeagueOfLegends\Riot\RiotSummonerManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use RiotAPI\LeagueAPI\Exceptions\RequestException;
use RiotAPI\LeagueAPI\Exceptions\ServerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FetchAccountsPUUID extends Command
{
    protected static $defaultName = 'lp:fetch:puuid';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RiotSummonerManager
     */
    private $riotSummonerManager;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, RiotSummonerManager $riotSummonerManager)
    {
        parent::__construct();
        $this->riotSummonerManager = $riotSummonerManager;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Updates missing puuids for players')
            ->addOption('force', 'f');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $this->logger->info('[FetchAccountsPUUID] Starting to fetch PUUIDs');
        foreach ($this->entityManager->getRepository(RiotAccount::class)->findAll() as $account) {
            if ($account->getEncryptedRiotId() && false === $input->getOption('force')) {
                continue;
            }
            $diff = $account->getUpdatedAt()->diff(new DateTime());
            if (!$diff->m && !$diff->d && $diff->h <= 1 && false === $input->getOption('force')) {
                continue;
            }
            try {
                /* @var RiotAccount $account */
                $summoner = $this->riotSummonerManager->getPuuidForName($account->getSummonerName());
                $account->setEncryptedPUUID($summoner->puuid);
                $account->setEncryptedAccountId($summoner->accountId);
                $account->setEncryptedRiotId($summoner->id);
                $this->entityManager->flush($account);
                $this->logger->info(sprintf('[FetchAccountsPUUID] Fetched PUUID for accout %s (%s)', $account->getUuid()->toString(), $account->getSummonerName()));
            } catch (RequestException $e) {
                $this->logger->critical(sprintf('[FetchAccountsPUUID] Did not update account %s (%s) because: %s', $account->getUuid()->toString(), $account->getSummonerName(), $e->getMessage()));
            } catch (ServerException $e) {
                $this->logger->critical(sprintf('Server exception %s', $e->getMessage()));
            }
        }
        $this->logger->info(sprintf('[FetchAccountsPUUID] Fetched PUUIDs in %s seconds.', microtime(true) - $start));
    }
}
