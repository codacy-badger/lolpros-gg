<?php

namespace App\Builder;

use App\Fetcher\LadderFetcher;
use App\Fetcher\MemberFetcher;

abstract class AMemberBuilder implements BuilderInterface
{
    /**
     * @var MemberFetcher
     */
    protected $memberFetcher;

    /**
     * @var LadderFetcher
     */
    protected $ladderFetcher;

    public function __construct(MemberFetcher $memberFetcher, LadderFetcher $ladderFetcher)
    {
        $this->memberFetcher = $memberFetcher;
        $this->ladderFetcher = $ladderFetcher;
    }

    protected function buildTeamMembers(array $memberships, bool $withRankings = true): ?array
    {
        $members = [];

        foreach ($memberships as $membership) {
            $player = $membership['player'];

            $member = [
                'uuid' => $player['uuid'],
                'name' => $player['name'],
                'slug' => $player['slug'],
                'current' => $membership['current'],
                'country' => $player['country'],
                'position' => $player['position'] ?? null,
                'join_date' => $membership['join_date'],
                'join_timestamp' => $membership['join_timestamp'],
                'leave_date' => $membership['leave_date'],
                'leave_timestamp' => $membership['leave_timestamp'],
            ];

            if ($withRankings) {
                $ladderPlayer = $this->ladderFetcher->fetchDocument($player['uuid']);

                if ($ladderPlayer) {
                    $player = $ladderPlayer->getData();
                    $member = array_merge($member, [
                        'profile_icon_id' => $player['account']['profile_icon_id'],
                        'summoner_name' => $player['account']['summoner_name'],
                        'tier' => $player['account']['tier'],
                        'rank' => $player['account']['rank'],
                        'league_points' => $player['account']['league_points'],
                        'score' => $player['score'],
                    ]);
                }
            }

            $members[] = $member;
        }

        return $members;
    }
}
