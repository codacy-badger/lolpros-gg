<?php

namespace App\Entity\LeagueOfLegends;

use App\Entity\Profile\Profile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="league__player")
 * @ORM\Entity(repositoryClass="App\Repository\LeagueOfLegends\PlayerRepository")
 */
class Player
{
    const POSITION_TOP = '10_top';
    const POSITION_JUNGLE = '20_jungle';
    const POSITION_MID = '30_mid';
    const POSITION_ADC = '40_adc';
    const POSITION_SUPPORT = '50_support';
    const POSITION_FILL = '60_fill';

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Exclude
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"league.post_player"})
     * @Assert\Choice(callback="getAvailablePositions", strict=true, )
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     *     "put_profile",
     *     "get_team_members",
     * })
     */
    protected $position;

    /**
     * @var Collection|RiotAccount[]
     * @ORM\OneToMany(targetEntity="App\Entity\LeagueOfLegends\RiotAccount", mappedBy="player")
     * @ORM\OrderBy({"score" = "DESC"})
     * @Serializer\Type("App\Entity\LeagueOfLegends\RiotAccount")
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     * })
     */
    protected $accounts;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     * @Serializer\Type("integer")
     */
    private $score = 0;

    /**
     * @var Profile
     * @ORM\OneToOne(targetEntity="App\Entity\Profile\Profile", inversedBy="leaguePlayer")
     * @Serializer\Type("App\Entity\Profile\Profile")
     */
    protected $profile;

    public function __construct()
    {
        $this->accounts = new ArrayCollection();
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function addAccount(RiotAccount $account): self
    {
        $this->accounts->add($account);

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition($position): self
    {
        $this->position = $position;

        return $this;
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getScore(): ?int
    {
        if (!$this->getAccounts()->count()) {
            return 0;
        }

        return $this->getBestAccount()->getScore();
    }

    public function getUuidAsString(): string
    {
        return $this->getProfile()->getUuidAsString();
    }

    /**
     * @Serializer\VirtualProperty
     */
    public function getBestAccount(): ?RiotAccount
    {
        if (!($accounts = $this->getAccounts())->count()) {
            return null;
        }

        $iterator = $accounts->getIterator();
        $iterator->uasort(function (RiotAccount $a, RiotAccount $b) {
            return ($a->getScore() > $b->getScore()) ? -1 : 1;
        });
        $accounts = new ArrayCollection(iterator_to_array($iterator));

        return $accounts->first();
    }

    public static function getAvailablePositions(): array
    {
        return [
            self::POSITION_TOP,
            self::POSITION_JUNGLE,
            self::POSITION_MID,
            self::POSITION_ADC,
            self::POSITION_SUPPORT,
            self::POSITION_FILL,
        ];
    }
}
