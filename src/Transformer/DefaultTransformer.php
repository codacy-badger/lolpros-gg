<?php

namespace App\Transformer;

use App\Entity\Document\Document as Logo;
use App\Entity\Team\Member;
use App\Entity\LeagueOfLegends\LeaguePlayer;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

abstract class DefaultTransformer implements DefaultTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    protected function buildMembers(Collection $memberships, $withRankings = true): ?array
    {
        if (!$memberships->count()) {
            return null;
        }

        $members = [];

        foreach ($memberships as $membership) {
            /** @var Member $membership */
            /** @var Player $player */
            $player = $membership->getProfile();
            $ranking = $player->getBestAccount() ? $player->getBestAccount()->getCurrentRanking() : null;

            $member = [
                'uuid' => $player->getUuidAsString(),
                'name' => $player->getName(),
                'slug' => $player->getSlug(),
                'current' => $membership->isCurrent(),
                'country' => $player->getCountry(),
                'join_date' => $membership->getJoinDate()->format(DateTime::ISO8601),
                'join_timestamp' => $membership->getJoinDate()->getTimestamp(),
                'leave_date' => $membership->getLeaveDate() ? $membership->getLeaveDate()->format(DateTime::ISO8601) : null,
                'leave_timestamp' => $membership->getLeaveDate() ? $membership->getLeaveDate()->getTimestamp() : null,
            ];

            //League player specifics
            if ($player instanceof Player && $withRankings) {
                $member = array_merge($member, [
                    'position' => $player->getPosition(),
                    'profile_icon_id' => $player->getBestAccount() ? $player->getBestAccount()->getProfileIconId() : null,
                    'summoner_name' => $player->getBestAccount() ? $player->getBestAccount()->getSummonerName() : null,
                    'tier' => $ranking ? $ranking->getTier() : null,
                    'rank' => $ranking ? $ranking->getRank() : null,
                    'league_points' => $ranking ? $ranking->getLeaguePoints() : null,
                    'score' => $ranking ? $ranking->getScore() : null,
                ]);
            }

            $members[] = $member;
        }

        return $members;
    }

    protected function buildLogo(?Logo $logo): ?array
    {
        if (!$logo) {
            return null;
        }

        return [
            'public_id' => $logo->getPublicId(),
            'version' => $logo->getVersion(),
            'url' => $logo->getUrl(),
        ];
    }
}
