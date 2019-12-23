<?php

namespace App\Builder;

use App\Entity\Core\Document\Document as Logo;
use App\Entity\Core\Team\Member;
use App\Entity\LeagueOfLegends\Player\Player;
use App\Fetcher\PlayerFetcher;
use App\Repository\Core\MemberRepository;
use App\Repository\Core\PlayerRepository;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlayersBuilder implements BuilderInterface
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

    public function __construct(PlayerFetcher $fetcher, PlayerRepository $playerRepository, MemberRepository $memberRepository)
    {
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

        foreach ($this->memberRepository->getCurrentPlayerMemberships($player) as $member) {
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
                'current_members' => $this->buildMembers($this->memberRepository->getCurrentTeamMemberships($team)),
                'previous_members' => $this->buildMembers($team->getSharedMemberships($member)),
            ]);
        }

        return $teams;
    }

    private function buildPreviousTeams(Player $player): array
    {
        $teams = [];

        foreach ($this->memberRepository->getPreviousPlayerMemberships($player) as $member) {
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
                'members' => $this->buildMembers($team->getMembersBetweenDates($member->getJoinDate(), $member->getLeaveDate())),
            ]);
        }

        return $teams;
    }

    protected function buildMembers(Collection $memberships): ?array
    {
        if (!$memberships->count()) {
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
                'current' => $membership->isCurrent(),
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
