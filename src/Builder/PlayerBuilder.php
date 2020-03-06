<?php

namespace App\Builder;

use App\Fetcher\LadderFetcher;
use App\Fetcher\MemberFetcher;
use App\Fetcher\ProfileFetcher;
use App\Repository\LeagueOfLegends\PlayerRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerBuilder extends AMemberBuilder implements BuilderInterface
{
    /**
     * @var ProfileFetcher
     */
    private $playerFetcher;

    /**
     * @var OptionsResolver
     */
    private $optionResolver;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    public function __construct(ProfileFetcher $playerFetcher, MemberFetcher $memberFetcher, LadderFetcher $ladderFetcher, PlayerRepository $playerRepository)
    {
        parent::__construct($memberFetcher, $ladderFetcher);
        $this->playerFetcher = $playerFetcher;
        $this->optionResolver = new OptionsResolver();
        $this->configureOptions($this->optionResolver);
        $this->playerRepository = $playerRepository;
    }

    public function build(array $options): array
    {
        $options = $this->optionResolver->resolve($options);
        $player = $this->playerFetcher->fetchOne($options);

        $player['teams'] = $this->buildTeams($player['uuid']);
        $player['previous_teams'] = $this->buildPreviousTeams($player['uuid']);
        $player['rankings'] = $this->buildPlayerRankings($player);

        return $player;
    }

    public function buildEmpty(): array
    {
        return [];
    }

    private function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setDefaults([
            'slug' => null,
            'uuid' => null,
            'riot_id' => null,
        ]);

        $resolver->setAllowedTypes('slug', ['string', 'null']);
        $resolver->setAllowedTypes('uuid', ['string', 'null']);
        $resolver->setAllowedTypes('riot_id', ['string', 'null']);

        return $resolver;
    }

    private function buildTeams(string $profileUuid): array
    {
        $teams = [];

        $memberships = $this->memberFetcher->fetch(['profile' => $profileUuid, 'current' => true]);
        foreach ($memberships as $member) {
            $team = $member['team'];
            array_push($teams, [
                'uuid' => $team['uuid'],
                'tag' => $team['tag'],
                'name' => $team['name'],
                'slug' => $team['slug'],
                'logo' => $team['logo'],
                'role' => $member['role'],
                'join_date' => $member['join_date'],
                'leave_date' => $member['leave_date'],
                'current_members' => $this->buildTeamMembers($this->memberFetcher->fetch(['team' => $team['uuid'], 'current' => true])),
                'previous_members' => $this->buildTeamMembers($this->memberFetcher->fetch([
                    'team' => $team['uuid'],
                    'current' => false,
                    'start' => $member['join_timestamp'],
                ]), false),
            ]);
        }

        return $teams;
    }

    private function buildPreviousTeams(string $profileUuid): array
    {
        $teams = [];

        $memberships = $this->memberFetcher->fetch([
            'profile' => $profileUuid,
            'current' => false,
        ]);
        foreach ($memberships as $member) {
            $team = $member['team'];
            array_push($teams, [
                'uuid' => $team['uuid'],
                'tag' => $team['tag'],
                'name' => $team['name'],
                'slug' => $team['slug'],
                'logo' => $team['logo'],
                'join_date' => $member['join_date'],
                'leave_date' => $member['leave_date'],
                'role' => $member['role'],
                'members' => $this->buildTeamMembers($this->memberFetcher->fetch([
                    'team' => $team['uuid'],
                    'start' => $member['join_timestamp'],
                    'end' => $member['leave_timestamp'],
                ]), false),
            ]);
        }

        return $teams;
    }

    private function buildPlayerRankings(array $player): array
    {
        $rankings = [];

        if ($player['league_player']) {
            $rankings['global'] = $this->playerRepository->getPlayersRankings($player['uuid']);
            $rankings['country'] = $this->playerRepository->getPlayersRankings($player['uuid'], null, $player['country']);
            $rankings['position'] = $this->playerRepository->getPlayersRankings($player['uuid'], $player['league_player']['position']);
            $rankings['country_position'] = $this->playerRepository->getPlayersRankings($player['uuid'], $player['league_player']['position'], $player['country']);
        }

        return $rankings;
    }
}
