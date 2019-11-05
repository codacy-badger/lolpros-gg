<?php

namespace App\Entity\LeagueOfLegends\Player;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LeagueOfLegends\RankingRepository")
 * @ORM\Table(name="player__league__ranking")
 */
class Ranking
{
    const QUEUE_TYPE_SOLO = 'RANKED_SOLO_5x5';
    const QUEUE_TYPE_FLEX = 'RANKED_FLEX_SR';
    const QUEUE_TYPE_3V3 = 'RANKED_FLEX_TT';

    const TIER_CHALLENGER = '00_challenger';
    const TIER_GRANDMASTER = '10_grandmaster';
    const TIER_MASTER = '20_master';
    const TIER_DIAMOND = '30_diamond';
    const TIER_PLATINUM = '40_platinum';
    const TIER_GOLD = '50_gold';
    const TIER_SILVER = '60_silver';
    const TIER_BRONZE = '70_bronze';
    const TIER_IRON = '80_iron';
    const TIER_UNRANKED = '90_unranked';

    const SEASON_8 = 'season_8';
    const PRE_SEASON_9 = 'pre_season_9';
    const SEASON_9 = 'season_9';
    const SEASON_9_V2 = 'season_9_v2';
    const PRE_SEASON_10 = 'pre_season_10';

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Exclude
     */
    protected $id;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     * @Serializer\Type("boolean")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    protected $best = false;

    /**
     * @var RiotAccount
     * @ORM\ManyToOne(targetEntity="App\Entity\LeagueOfLegends\Player\RiotAccount", inversedBy="rankings")
     * @Serializer\Type("string")
     */
    protected $owner;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Serializer\Type("DateTime")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    protected $createdAt;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     */
    protected $queueType;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    protected $tier;

    /**
     * @var string
     * @ORM\Column(type="integer", options={"default"=0})
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    protected $rank = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     * @Serializer\Type("integer")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    protected $leaguePoints = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     * @Serializer\Type("integer")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    protected $wins = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     * @Serializer\Type("integer")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    protected $losses = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     * @Serializer\Type("integer")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    private $score = 0;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_riot_account_rankings",
     * })
     */
    private $season;

    public function isBest(): bool
    {
        return $this->best;
    }

    public function setBest(bool $best): self
    {
        $this->best = $best;

        return $this;
    }

    public function getOwner(): ?RiotAccount
    {
        return $this->owner;
    }

    public function setOwner(?RiotAccount $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getQueueType(): string
    {
        return $this->queueType;
    }

    public function setQueueType(string $queueType): self
    {
        $this->queueType = $queueType;

        return $this;
    }

    public function getTier(): string
    {
        return $this->tier;
    }

    public function setTier(string $tier): self
    {
        $this->tier = $tier;

        return $this;
    }

    public function getRank(): string
    {
        return $this->rank;
    }

    public function setRank(string $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getLeaguePoints(): int
    {
        return $this->leaguePoints;
    }

    public function setLeaguePoints(int $leaguePoints): self
    {
        $this->leaguePoints = $leaguePoints;

        return $this;
    }

    public function getWins(): int
    {
        return $this->wins;
    }

    public function setWins(int $wins): self
    {
        $this->wins = $wins;

        return $this;
    }

    public function getLosses(): int
    {
        return $this->losses;
    }

    public function setLosses(int $losses): self
    {
        $this->losses = $losses;

        return $this;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getSeason(): string
    {
        return $this->season;
    }

    public function setSeason(string $season): self
    {
        $this->season = $season;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public static function getAvailableTiers(): array
    {
        return [
            self::TIER_CHALLENGER,
            self::TIER_GRANDMASTER,
            self::TIER_MASTER,
            self::TIER_DIAMOND,
            self::TIER_PLATINUM,
            self::TIER_GOLD,
            self::TIER_SILVER,
            self::TIER_BRONZE,
            self::TIER_UNRANKED,
        ];
    }
}
