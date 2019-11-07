<?php

namespace App\Transformer;

use App\Entity\Core\Document\Document as Logo;
use App\Entity\Core\Team\Member;
use App\Entity\LeagueOfLegends\Player\Player;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

abstract class DefaultTransformer implements DefaultTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function buildMembers(ArrayCollection $memberships): ?array
    {
        if (!$memberships->count()) {
            return null;
        }

        $members = [];

        /** @var Member $membership */
        foreach ($memberships as $membership) {
            $identity = $membership->getIdentity();

            $member = [
                'uuid' => $identity->getUuidAsString(),
                'name' => $identity->getName(),
                'slug' => $identity->getSlug(),
                'current' => $membership->isCurrent(),
                'country' => $identity->getCountry(),
                'join_date' => $membership->getJoinDate()->format(DateTime::ISO8601),
                'join_timestamp' => $membership->getJoinDate()->getTimestamp(),
                'leave_date' => $membership->getLeaveDate() ? $membership->getLeaveDate()->format(DateTime::ISO8601) : null,
                'leave_timestamp' => $membership->getLeaveDate() ? $membership->getLeaveDate()->getTimestamp() : null,
            ];

            //League player specifics
            if ($identity instanceof Player) {
                $ranking = $identity->getBestAccount() ? $identity->getBestAccount()->getCurrentRanking() : null;
                $member = array_merge($member, [
                    'position' => $identity->getPosition(),
                    'summoner_name' => $identity->getMainAccount() ? $identity->getMainAccount()->getCurrentSummonerName()->getName() : null,
                    'profile_icon_id' => $identity->getMainAccount() ? $identity->getMainAccount()->getProfileIconId() : null,
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
