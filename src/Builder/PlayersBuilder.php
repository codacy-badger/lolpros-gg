<?php

namespace App\Builder;

use App\Entity\Core\Team\Member;
use App\Entity\LeagueOfLegends\Player\Player;
use App\Fetcher\PlayerFetcher;
use App\Repository\Core\MemberRepository;
use App\Repository\Core\PlayerRepository;
use App\Transformer\PlayerTransformer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayersBuilder extends PlayerTransformer implements BuilderInterface
{
    /**
     * @var PlayerFetcher
     */
    private $fetcher;

    /**
     * @var PlayerRepository
     */
    private $playerRepository;

    /**
     * @var MemberRepository
     */
    private $memberRepository;

    /**
     * @var OptionsResolver
     */
    private $optionResolver;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, PlayerFetcher $fetcher, PlayerRepository $playerRepository, MemberRepository $memberRepository)
    {
        parent::__construct($entityManager, $logger);
        $this->fetcher = $fetcher;
        $this->playerRepository = $playerRepository;
        $this->memberRepository = $memberRepository;
        $this->optionResolver = new OptionsResolver();
        $this->configureOptions($this->optionResolver);
    }

    public function build(array $options): array
    {
        $options = $this->optionResolver->resolve($options);
        $playerArray = $this->fetcher->fetchOne($options);
        /** @var Player $player */
        $player = $this->playerRepository->findOneBy(['uuid' => $playerArray['uuid']]);
        $playerArray['teams'] = $this->buildTeams($player);
        $playerArray['previous_teams'] = $this->buildPreviousTeams($player);

        return $playerArray;
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

    private function buildTeams(Player $player): array
    {
        $teams = [];

        foreach ($player->getCurrentMemberships() as $member) {
            /** @var Member $member */
            $team = $member->getTeam();
            array_push($teams, [
                'uuid' => $team->getUuidAsString(),
                'tag' => $team->getTag(),
                'name' => $team->getName(),
                'slug' => $team->getSlug(),
                'logo' => $this->buildLogo($team->getLogo()),
                'join_date' => $member->getJoinDate()->format(DateTime::ISO8601),
                'leave_date' => $member->getLeaveDate() ? $member->getLeaveDate()->format(DateTime::ISO8601) : null,
                'current_members' => $this->buildMembers($team->getCurrentMemberships()),
                'previous_members' => $this->buildMembers($team->getSharedMemberships($member), false),
            ]);
        }

        return $teams;
    }

    private function buildPreviousTeams(Player $player): array
    {
        $teams = [];

        foreach ($player->getPreviousMemberships() as $member) {
            /** @var Member $member */
            $team = $member->getTeam();
            array_push($teams, [
                'uuid' => $team->getUuidAsString(),
                'tag' => $team->getTag(),
                'name' => $team->getName(),
                'slug' => $team->getSlug(),
                'logo' => $this->buildLogo($team->getLogo()),
                'join_date' => $member->getJoinDate()->format(DateTime::ISO8601),
                'leave_date' => $member->getLeaveDate() ? $member->getLeaveDate()->format(DateTime::ISO8601) : null,
                'members' => $this->buildMembers($team->getMembersBetweenDates($member->getJoinDate(), $member->getLeaveDate()), false),
            ]);
        }

        return $teams;
    }
}
