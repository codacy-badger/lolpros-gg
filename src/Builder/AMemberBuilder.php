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
            $profile = $membership['profile'];

            $member = [
                'uuid' => $profile['uuid'],
                'name' => $profile['name'],
                'slug' => $profile['slug'],
                'current' => $membership['current'],
                'country' => $profile['country'],
                'role' => $membership['role'],
                'position' => $profile['position'] ?? null,
                'join_date' => $membership['join_date'],
                'join_timestamp' => $membership['join_timestamp'],
                'leave_date' => $membership['leave_date'],
                'leave_timestamp' => $membership['leave_timestamp'],
            ];

            if ($withRankings) {
                $ladderPlayer = $this->ladderFetcher->fetchDocument($profile['uuid']);

                if ($ladderPlayer) {
                    $player = $ladderPlayer->getData();
                    $account = $player['account'];
                    $member = array_merge($member, [
                        'profile_icon_id' => $account['profile_icon_id'],
                        'summoner_name' => $account['summoner_name'],
                        'tier' => $account['tier'],
                        'rank' => $account['rank'],
                        'league_points' => $account['league_points'],
                        'score' => $player['score'],
                    ]);
                }
            }

            $members[] = $member;
        }

        return $members;
    }
}
