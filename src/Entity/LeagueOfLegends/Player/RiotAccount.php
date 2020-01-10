<?php

namespace App\Entity\LeagueOfLegends\Player;

use App\Entity\StringUuidTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="player__league__riot_account")
 * @ORM\Entity(repositoryClass="App\Repository\LeagueOfLegends\RiotAccountRepository")
 */
class RiotAccount
{
    use StringUuidTrait;

    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Exclude
     */
    protected $id;

    /**
     * @var UuidInterface
     * @ORM\Column(type="uuid", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_players",
     *     "league.get_player_riot_accounts",
     *     "league.get_riot_account",
     *     "league.get_riot_accounts",
     * })
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_player_riot_accounts",
     *     "league.post_riot_account",
     * })
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    protected $riotId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    protected $accountId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\SerializedName("ecrypted_puuid")
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    protected $encryptedPUUID;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_player_riot_accounts",
     *     "league.get_riot_account",
     *     "league.get_riot_accounts",
     * })
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    protected $encryptedRiotId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_riot_account",
     * })
     * @Assert\NotNull
     * @Assert\NotBlank
     */
    protected $encryptedAccountId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_riot_account",
     * })
     */
    protected $profileIconId;

    /**
     * @var Player
     * @ORM\ManyToOne(targetEntity="App\Entity\LeagueOfLegends\Player\Player", inversedBy="accounts")
     * @Serializer\Type("App\Entity\LeagueOfLegends\Player\Player")
     * @Serializer\Groups({
     *     "league.get_riot_account",
     * })
     */
    protected $player;

    /**
     * @var Collection|SummonerName[]
     * @ORM\OneToMany(targetEntity="App\Entity\LeagueOfLegends\Player\SummonerName", mappedBy="owner")
     * @ORM\OrderBy({"createdAt"="DESC"})
     */
    protected $summonerNames;

    /**
     * @var Collection|Ranking[]
     * @ORM\OneToMany(targetEntity="App\Entity\LeagueOfLegends\Player\Ranking", mappedBy="owner")
     * @ORM\OrderBy({"createdAt"="DESC"})
     */
    protected $rankings;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     * @Serializer\Type("DateTime")
     * @Serializer\Groups({
     *     "league.get_player_riot_accounts",
     * })
     */
    protected $updatedAt;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     * @Serializer\Type("integer")
     */
    private $score = 0;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=1})
     * @Serializer\Type("integer")
     */
    protected $summonerLevel;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->summonerNames = new ArrayCollection();
        $this->rankings = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function getRiotId(): ?string
    {
        return $this->riotId;
    }

    public function setRiotId(string $riotId): self
    {
        $this->riotId = $riotId;

        return $this;
    }

    public function getAccountId(): ?string
    {
        return $this->accountId;
    }

    public function setAccountId(string $accountId): self
    {
        $this->accountId = $accountId;

        return $this;
    }

    public function getEncryptedPUUID(): ?string
    {
        return $this->encryptedPUUID;
    }

    public function setEncryptedPUUID(string $encryptedPUUID): self
    {
        $this->encryptedPUUID = $encryptedPUUID;

        return $this;
    }

    public function getEncryptedRiotId(): ?string
    {
        return $this->encryptedRiotId;
    }

    public function setEncryptedRiotId(string $encryptedRiotId): self
    {
        $this->encryptedRiotId = $encryptedRiotId;

        return $this;
    }

    public function getEncryptedAccountId(): ?string
    {
        return $this->encryptedAccountId;
    }

    public function setEncryptedAccountId(string $encryptedAccountId): self
    {
        $this->encryptedAccountId = $encryptedAccountId;

        return $this;
    }

    public function getProfileIconId(): ?string
    {
        return $this->profileIconId;
    }

    public function setProfileIconId(string $profileIconId): self
    {
        $this->profileIconId = $profileIconId;

        return $this;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function setPlayer(Player $player): self
    {
        $this->player = $player;

        return $this;
    }

    public function addSummonerName(SummonerName $summonerName): self
    {
        $this->summonerNames->add($summonerName);

        return $this;
    }

    public function removeSummonerName(SummonerName $summonerName): self
    {
        $this->summonerNames->remove($summonerName);

        return $this;
    }

    public function addRanking(Ranking $ranking): self
    {
        $this->rankings->add($ranking);

        return $this;
    }

    public function removeRanking(Ranking $ranking): self
    {
        $this->rankings->remove($ranking);
        $ranking->setOwner(null);

        return $this;
    }

    public function getRankings(): Collection
    {
        return $this->rankings;
    }

    public function setRankings(Collection $rankings): self
    {
        $this->rankings = $rankings;

        return $this;
    }

    public function getSummonerNames(): Collection
    {
        return $this->summonerNames;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getScore(): int
    {
        return $this->getCurrentRanking() ? $this->getCurrentRanking()->getScore() : 0;
    }

    public function getSummonerLevel(): int
    {
        return $this->summonerLevel;
    }

    public function setSummonerLevel(int $summonerLevel): self
    {
        $this->summonerLevel = $summonerLevel;

        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Groups({
     *     "league.get_player_riot_accounts",
     *     "league.get_riot_account",
     *     "league.get_riot_accounts",
     * })
     */
    public function getSummonerName(): string
    {
        return $this->getCurrentSummonerName()->getName();
    }

    public function getCurrentSummonerName(): ?SummonerName
    {
        if (!$this->summonerNames->count()) {
            return null;
        }

        return $this->summonerNames->filter(function (SummonerName $summonerName) {
            return $summonerName->isCurrent();
        })->first();
    }

    /**
     * @Serializer\VirtualProperty
     * @Serializer\Groups({
     *     "league.get_player_riot_accounts",
     * })
     */
    public function getCurrentRanking(): ?Ranking
    {
        return $this->rankings->count() ? $this->rankings->first() : null;
    }

    public function getLatestRanking(string $season = Ranking::SEASON_10): ?Ranking
    {
        $rankings = $this->rankings;
        $current = $rankings->filter(function (Ranking $ranking) use ($season) {
            return $season === $ranking->getSeason();
        });

        return $current->count() ? $current->first() : null;
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getBestRanking(string $season = Ranking::SEASON_10): ?Ranking
    {
        $rankings = $this->rankings;
        $best = $rankings->filter(function (Ranking $ranking) use ($season) {
            return $ranking->isBest() && $season === $ranking->getSeason();
        });

        return $best->count() ? $best->first() : null;
    }
}
