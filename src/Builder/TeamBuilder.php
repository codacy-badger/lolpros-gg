<?php

namespace App\Builder;

use App\Fetcher\LadderFetcher;
use App\Fetcher\MemberFetcher;
use App\Fetcher\TeamFetcher;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamBuilder extends AMemberBuilder implements BuilderInterface
{
    /**
     * @var TeamFetcher
     */
    private $teamFetcher;

    /**
     * @var OptionsResolver
     */
    private $optionResolver;

    public function __construct(TeamFetcher $teamFetcher, MemberFetcher $memberFetcher, LadderFetcher $ladderFetcher)
    {
        parent::__construct($memberFetcher, $ladderFetcher);
        $this->teamFetcher = $teamFetcher;
        $this->optionResolver = new OptionsResolver();
        $this->configureOptions($this->optionResolver);
    }

    public function build(array $options): array
    {
        $options = $this->optionResolver->resolve($options);
        $team = $this->teamFetcher->fetchOne($options);

        $team['current_members'] = $this->buildTeamMembers($this->memberFetcher->fetch(['team' => $team['uuid'], 'current' => true]));
        $team['previous_members'] = $this->buildTeamMembers($this->memberFetcher->fetch(['team' => $team['uuid'], 'current' => false]), false);
        $team['active'] = (bool) count($team['current_members']);

        return $team;
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
}
