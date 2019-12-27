<?php

namespace App\Transformer;

use App\Entity\Core\Player\Player;
use App\Entity\Core\Team\Member;
use App\Entity\Core\Team\Team;
use App\Indexer\Indexer;
use DateTime;
use Elastica\Document;

class MemberTransformer extends DefaultTransformer
{
    public function fetchAndTransform($document, array $fields): ?Document
    {
        $player = $this->entityManager->getRepository(Member::class)->findOneBy(['uuid' => $document['uuid']]);

        if (!$player instanceof Member) {
            return null;
        }

        return $this->transform($player, $fields);
    }

    public function transform($member, array $fields)
    {
        if (!$member instanceof Member) {
            return null;
        }

        $document = [
            'uuid' => $member->getUuidAsString(),
            'player' => $this->buildPlayer($member->getPlayer()),
            'team' => $this->buildTeam($member->getTeam()),
            'type' => $member->getRole(),
            'join_date' => $member->getJoinDate()->format(DateTime::ISO8601),
            'join_timestamp' => $member->getJoinDate()->getTimestamp(),
            'leave_date' => $member->getLeaveDate() ? $member->getLeaveDate()->format(DateTime::ISO8601) : null,
            'leave_timestamp' => $member->getLeaveDate() ? $member->getLeaveDate()->getTimestamp() : null,
            'current' => $member->isCurrent(),
            'timestamp' => $member->getCreatedAt()->format(DateTime::ISO8601),
        ];

        return new Document($member->getUuidAsString(), $document, Indexer::INDEX_TYPE_MEMBER, Indexer::INDEX_MEMBERS);
    }

    private function buildPlayer(Player $player)
    {
        $player = [
            'uuid' => $player->getUuidAsString(),
            'name' => $player->getName(),
            'slug' => $player->getSlug(),
            'country' => $player->getCountry(),
            'position' => $player->getPosition() ?? null,
        ];

        return $player;
    }

    private function buildTeam(Team $team)
    {
        $region = $team->getRegion();

        $team = [
            'uuid' => $team->getUuidAsString(),
            'name' => $team->getName(),
            'slug' => $team->getSlug(),
            'tag' => $team->getTag(),
            'logo' => $this->buildLogo($team->getLogo()),
            'region' => [
                'uuid' => $region->getUuidAsString(),
                'name' => $region->getName(),
                'slug' => $region->getSlug(),
                'shorthand' => $region->getShorthand(),
                'logo' => $this->buildLogo($region->getLogo()),
            ],
        ];

        return $team;
    }
}
