<?php

namespace App\Transformer;

use App\Entity\Core\Document\Document as Logo;
use App\Entity\Core\Team\Member;
use App\Entity\LeagueOfLegends\Player\Player;
use DateTime;
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

    protected function buildMembers($memberships): ?array
    {
        if (!count($memberships)) {
            return null;
        }

        $members = [];

        foreach ($memberships as $membership) {
            /** @var Member $membership */
            /** @var Player $player */
            $player = $membership->getPlayer();
            $ranking = $player->getBestAccount() ? $player->getBestAccount()->getCurrentRanking() : null;

            $member = [
                'uuid' => $player->getUuidAsString(),
                'name' => $player->getName(),
                'slug' => $player->getSlug(),
                'current' => (bool) $membership->getLeaveDate(),
                'country' => $player->getCountry(),
                'join_date' => $membership->getJoinDate()->format(DateTime::ISO8601),
                'join_timestamp' => $membership->getJoinDate()->getTimestamp(),
                'leave_date' => $membership->getLeaveDate() ? $membership->getLeaveDate()->format(DateTime::ISO8601) : null,
                'leave_timestamp' => $membership->getLeaveDate() ? $membership->getLeaveDate()->getTimestamp() : null,
            ];

            //League player specifics
            if ($player instanceof Player) {
                $member = array_merge($member, [
                    'position' => $player->getPosition(),
                    'profile_icon_id' => $player->getBestAccount() ? $player->getBestAccount()->getProfileIconId() : null,
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
