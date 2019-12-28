<?php

namespace App\Builder;

use App\Fetcher\LadderFetcher;
use App\Fetcher\MemberFetcher;
use App\Fetcher\PlayerFetcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayerBuilder extends AMemberBuilder implements BuilderInterface
{
    /**
     * @var PlayerFetcher
     */
    private $playerFetcher;

    /**
     * @var OptionsResolver
     */
    private $optionResolver;

    public function __construct(PlayerFetcher $playerFetcher, MemberFetcher $memberFetcher, LadderFetcher $ladderFetcher)
    {
        parent::__construct($memberFetcher, $ladderFetcher);
        $this->playerFetcher = $playerFetcher;
        $this->optionResolver = new OptionsResolver();
        $this->configureOptions($this->optionResolver);
    }

    public function build(array $options): array
    {
        $options = $this->optionResolver->resolve($options);
        $player = $this->playerFetcher->fetchOne($options);

        $player['teams'] = $this->buildTeams($player['uuid']);
        $player['previous_teams'] = $this->buildPreviousTeams($player['uuid']);

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
        ]);

        $resolver->setAllowedTypes('slug', ['string', 'null']);
        $resolver->setAllowedTypes('uuid', ['string', 'null']);

        return $resolver;
    }

    private function buildTeams(string $playerUuid): array
    {
        $teams = [];

        $memberships = $this->memberFetcher->fetch(['player' => $playerUuid, 'current' => true]);
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
                'current_members' => $this->buildTeamMembers($this->memberFetcher->fetch(['team' => $team['uuid'], 'current' => true])),
                'previous_members' => $this->buildTeamMembers($this->memberFetcher->fetch(['team' => $team['uuid'], 'current' => false])),
            ]);
        }

        return $teams;
    }

    private function buildPreviousTeams(string $playerUuid): array
    {
        $teams = [];

        $memberships = $this->memberFetcher->fetch([
            'player' => $playerUuid,
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
                'members' => $this->buildTeamMembers($this->memberFetcher->fetch([
                    'team' => $team['uuid'],
                    'start' => $member['join_timestamp'],
                    'end' => $member['leave_timestamp'],
                ]), false),
            ]);
        }

        return $teams;
    }
}
