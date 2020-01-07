<?php

namespace App\Manager\LeagueOfLegends;

use App\Entity\LeagueOfLegends\Player;
use App\Entity\LeagueOfLegends\Ranking;
use App\Entity\LeagueOfLegends\RiotAccount;
use App\Entity\Profile\Profile;
use App\Event\LeagueOfLegends\RiotAccountEvent;
use App\Exception\EntityNotDeletedException;
use App\Exception\EntityNotUpdatedException;
use App\Exception\LeagueOfLegends\AccountRecentlyUpdatedException;
use App\Manager\DefaultManager;
use App\Manager\LeagueOfLegends\Riot\RiotSummonerManager;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use RiotAPI\LeagueAPI\Exceptions\ServerLimitException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class RiotAccountManager extends DefaultManager
{
    /**
     * @var RankingManager
     */
    private $rankingsManager;

    /**
     * @var RiotSummonerManager
     */
    private $riotSummonerManager;

    /**
     * @var SummonerNameManager
     */
    private $summonerNamesManager;

    /**
     * RiotAccountsManager constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        RankingManager $rankingsManager,
        RiotSummonerManager $riotSummonerManager,
        SummonerNameManager $summonerNamesManager
    ) {
        parent::__construct($entityManager, $logger, $eventDispatcher);
        $this->rankingsManager = $rankingsManager;
        $this->riotSummonerManager = $riotSummonerManager;
        $this->summonerNamesManager = $summonerNamesManager;
    }

    /**
     * Checks if an account already exists with the provided riot ID.
     */
    public function accountExists($id)
    {
        return $this->entityManager->getRepository(RiotAccount::class)->findOneBy([
            'encryptedRiotId' => $id,
        ]);
    }

    public function update(RiotAccount $riotAccount): RiotAccount
    {
        try {
            $this->entityManager->flush($riotAccount);
            $this->eventDispatcher->dispatch(new RiotAccountEvent($riotAccount), RiotAccountEvent::UPDATED);

            return $riotAccount;
        } catch (Exception $e) {
            $this->logger->error('[RiotAccountsManager] Could not update riotAccount {uuid} because of {reason}', [
                'uuid' => $riotAccount->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotUpdatedException(RiotAccount::class, $riotAccount->getUuidAsString(), $e->getMessage());
        }
    }

    public function refreshRiotAccount(RiotAccount $riotAccount): RiotAccount
    {
        try {
            $diff = $riotAccount->getUpdatedAt()->diff(new DateTime());
            if (!$diff->m && !$diff->d && $diff->h <= 1) {
                throw new AccountRecentlyUpdatedException($diff);
            }

            $this->summonerNamesManager->updateSummonerName($riotAccount);
            $this->rankingsManager->updateRanking($riotAccount);
            $riotAccount->setUpdatedAt(new DateTime());
            $this->entityManager->flush($riotAccount);

            $this->eventDispatcher->dispatch(new RiotAccountEvent($riotAccount), RiotAccountEvent::UPDATED);
            $this->logger->notice(sprintf('[RiotAccountsManager::refreshRiotAccount] Successfully updated account %s (%s).', $riotAccount->getUuidAsString(), $riotAccount->getSummonerName()));

            return $riotAccount;
        } catch (AccountRecentlyUpdatedException  $e) {
            $this->logger->notice(sprintf('[RiotAccountsManager::refreshRiotAccount] Did not update account %s (%s) because it was already updated.', $riotAccount->getUuidAsString(), $riotAccount->getSummonerName()));

            throw $e;
        } catch (ServerLimitException $e) {
            $this->logger->notice(sprintf('[RiotAccountsManager::refreshRiotAccount] Could not update account %s (%s) because the API rate limit was reached.', $riotAccount->getUuidAsString(), $riotAccount->getSummonerName()));

            throw $e;
        } catch (Exception $e) {
            $this->logger->error(sprintf('[RiotAccountsManager::refreshRiotAccount] Could not update account %s (%s) because of {reason}.', $riotAccount->getUuidAsString(), $riotAccount->getSummonerName()), [
                'uuid' => $riotAccount->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new BadRequestHttpException($e->getMessage(), null, $e->getCode());
        }
    }

    public function createRiotAccount(RiotAccount $riotAccountData, Profile $profile): RiotAccount
    {
        try {
            $summoner = $this->riotSummonerManager->getForId($riotAccountData->getRiotId());

            $player = $profile->getLeaguePlayer();
            if (!$player) {
                $player = new Player();
                $player->setProfile($profile);
                $this->entityManager->persist($player);
            }

            $riotAccount = new RiotAccount();
            $riotAccount->setRiotId($riotAccountData->getRiotId());
            $riotAccount->setAccountId($summoner->accountId);
            $riotAccount->setEncryptedPUUID($summoner->puuid);
            $riotAccount->setEncryptedAccountId($summoner->accountId);
            $riotAccount->setEncryptedRiotId($summoner->id);
            $riotAccount->setProfileIconId($summoner->profileIconId);
            $riotAccount->setSummonerLevel($summoner->summonerLevel);
            $riotAccount->setPlayer($player);
            $this->entityManager->persist($riotAccount);

            $summonerName = SummonerNameManager::createFromSummoner($summoner);
            $summonerName->setCurrent(true);
            $summonerName->setOwner($riotAccount);
            $riotAccount->addSummonerName($summonerName);
            $this->entityManager->persist($summonerName);

            $ranking = $this->rankingsManager->getForRiotAccount($riotAccount);
            $ranking->setOwner($riotAccount);
            $ranking->setSeason(Ranking::SEASON_10);
            $riotAccount->addRanking($ranking);
            $riotAccount->setScore($ranking->getScore());
            $this->entityManager->persist($ranking);

            $player->addAccount($riotAccount);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new RiotAccountEvent($riotAccount), RiotAccountEvent::CREATED);

            return $riotAccount;
        } catch (Exception $e) {
            $this->logger->error('[RiotAccountsManager] Could not create RiotAccount for profile {uuid} because of {reason}', [
                'uuid' => $profile->getUuidAsString() ?? null,
                'reason' => $e->getMessage(),
            ]);

            throw new BadRequestHttpException($e->getMessage());
        }
    }

    public function delete(RiotAccount $riotAccount)
    {
        $this->logger->debug('[RiotAccountsManager::delete] Deleting RiotAccount {uuid}', ['uuid' => $riotAccount->getUuidAsString()]);
        try {
            foreach ($riotAccount->getRankings() as $ranking) {
                $this->entityManager->remove($ranking);
            }
            foreach ($riotAccount->getSummonerNames() as $summonerName) {
                $this->entityManager->remove($summonerName);
            }

            $this->entityManager->remove($riotAccount);
            $this->entityManager->flush();

            $this->eventDispatcher->dispatch(new RiotAccountEvent($riotAccount), RiotAccountEvent::DELETED);
        } catch (Exception $e) {
            $this->logger->error('[RiotAccountsManager::delete] Could not delete RiotAccount {uuid} because of {reason}', [
                'uuid' => $riotAccount->getUuidAsString(),
                'reason' => $e->getMessage(),
            ]);

            throw new EntityNotDeletedException(RiotAccount::class, $riotAccount->getUuidAsString(), $e->getMessage());
        }
    }
}
