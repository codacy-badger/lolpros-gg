<?php

namespace App\Command;

use App\Entity\LeagueOfLegends\Player\Player;
use App\Entity\LeagueOfLegends\Player\Ranking;
use App\Entity\LeagueOfLegends\Player\RiotAccount;
use App\Manager\LeagueOfLegends\Player\RankingManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecalculateScores extends Command
{
    protected static $defaultName = 'lp:calculate:scores';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);
        $rankingRepository = $this->entityManager->getRepository(Ranking::class);
        foreach ($rankingRepository->findAll() as $ranking) {
            /** @var Ranking $ranking */
            $score = RankingManager::calculateScore($ranking);
            if ($ranking->getScore() != $score) {
                $output->writeln(sprintf('<error>[RANKING] Score was invalid: %d <> %d</error>', $ranking->getScore(), $score));
                $ranking->setScore(RankingManager::calculateScore($ranking));
                $this->entityManager->flush($ranking);
            } else {
                $output->writeln(sprintf('<info>Calculated score %d for ranking %s %d - %dLP</info>', $ranking->getScore(), $ranking->getTier(), $ranking->getRank(), $ranking->getLeaguePoints()));
            }
            $this->entityManager->clear();
        }
        foreach ($this->entityManager->getRepository(RiotAccount::class)->findAll() as $account) {
            /** @var RiotAccount $account */
            $score = $account->getCurrentRanking()->getScore();
            if ($account->getScore() != $score) {
                $output->writeln(sprintf('<error>[ACCOUNT] Score was invalid for account %s: %d <> %d</error>', $account->getUuid()->toString(), $account->getScore(), $score));
                $account->setScore($score);
                $this->entityManager->flush($account);
            } else {
                $output->writeln(sprintf('<info>[ACCOUNT] Score %d is valid for account %s</info>', $account->getScore(), $account->getUuid()->toString()));
            }
            $this->entityManager->clear();
        }
        foreach ($this->entityManager->getRepository(Player::class)->findAll() as $player) {
            /** @var Player $player */
            $account = $player->getBestAccount();
            if ($account) {
                $score = $account->getScore();
                if ($player->getScore() != $score) {
                    $player->setScore($score);
                    $this->entityManager->flush($player);
                    $output->writeln(sprintf('<error>[PLAYER] Score was invalid for player %s: %d <> %d</error>', $player->getName(), $player->getScore(), $score));
                } else {
                    $output->writeln(sprintf('<info>[PLAYER] Score %d is valid for player %s</info>', $player->getScore(), $player->getName()));
                }
            } else {
                $output->writeln(sprintf('<comment>No account found for player %s</comment>', $player->getName()));
            }
            $this->entityManager->clear();
        }

        $this->logger->info(sprintf('[RecalculateScores] Recalculated all scores in %s seconds.', microtime(true) - $start));

        foreach ($this->entityManager->getRepository(RiotAccount::class)->findAll() as $account) {
            $best = null;
            /* @var RiotAccount $account */
            foreach ($account->getRankings() as $ranking) {
                /* @var Ranking $ranking */
                if (!$best instanceof Ranking) {
                    $best = $ranking;
                    continue;
                }
                if ($best->getScore() < $ranking->getScore()) {
                    $best = $ranking;
                }
                $ranking->setBest(false);
            }
            $best->setBest(true);
            $this->entityManager->flush();
            $output->writeln(sprintf('<info>Updated best score for account %s %s</info>', $account->getSummonerName(), $best->getScore()));
        }

        $this->logger->info(sprintf('[RecalculateScores] Recalculated all best accounts in %s seconds.', microtime(true) - $start));
    }
}
