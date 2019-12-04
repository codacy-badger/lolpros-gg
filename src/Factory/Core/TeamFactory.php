<?php

namespace App\Factory\Core;

use App\Entity\Core\Team\Member;
use App\Entity\LeagueOfLegends\Player\Player;
use App\Model\Core\PlayerTeam;

class TeamFactory
{
    public static function createFromMembership(Member $member): PlayerTeam
    {
        $team = new PlayerTeam();

        $team->joinDate = $member->getJoinDate();
        $team->leaveDate = $member->getLeaveDate();

        $teamEntity = $member->getTeam();
        $team->uuid = $teamEntity->getUuid();
        $team->name = $teamEntity->getName();
        $team->slug = $teamEntity->getSlug();
        $team->tag = $teamEntity->getTag();
        $logo = $teamEntity->getLogo();
        $team->logo = $logo ? [
            'public_id' => $logo->getPublicId(),
            'version' => $logo->getVersion(),
            'url' => $logo->getUrl(),
        ] : null;
        $team->creationDate = $teamEntity->getCreationDate();
        $team->disbandDate = $teamEntity->getDisbandDate();
        $team->members = [];

        $members = $teamEntity->getMembersBetweenDates($member->getJoinDate(), $member->getLeaveDate(), $member->getPlayer()->getPosition());

        foreach ($members as $teamMember) {
            /** @var Member $teamMember */
            /** @var Player $player */
            $player = $teamMember->getPlayer();
            if ($teamMember->getUuid() !== $member->getUuid()) {
                array_push($team->members, [
                    'name' => $player->getName(),
                    'uuid' => $player->getUuidAsString(),
                    'slug' => $player->getSlug(),
                    'country' => $player->getCountry(),
                    'join_date' => $teamMember->getJoinDate(),
                    'leave_date' => $teamMember->getLeaveDate(),
                    'position' => $player->getPosition(),
                    'role' => $teamMember->getRole(),
                ]);
            }
        }

        return $team;
    }
}
