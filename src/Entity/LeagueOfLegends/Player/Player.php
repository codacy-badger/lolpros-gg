<?php

namespace App\Entity\LeagueOfLegends\Player;

use App\Entity\Core\Identity\Identity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LeagueOfLegends\PlayerRepository")
 * @ORM\Table(name="player__player")
 */
class Player extends Identity
{
    const POSITION_TOP = '10_top';
    const POSITION_JUNGLE = '20_jungle';
    const POSITION_MID = '30_mid';
    const POSITION_ADC = '40_adc';
    const POSITION_SUPPORT = '50_support';

    /**
     * @var Collection|RiotAccount[]
     * @ORM\OneToMany(targetEntity="App\Entity\LeagueOfLegends\Player\RiotAccount", mappedBy="player")
     * @ORM\OrderBy({"smurf" = "ASC", "score" = "DESC"})
     * @Serializer\Type("App\Entity\LeagueOfLegends\Player\RiotAccount")
     * @Serializer\Groups({
     *     "league.get_players",
     * })
     */
    protected $accounts;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"league.post_player"})
     * @Assert\Choice(callback="getAvailablePositions", strict=true)
     * @Serializer\Groups({
     *     "league.get_players",
     *     "league.get_player",
     *     "league.put_player",
     * })
     */
    protected $position;

    /**
     * @var int
     * @ORM\Column(type="integer", options={"default"=0})
     * @Serializer\Type("integer")
     */
    private $score = 0;

    public function __construct()
    {
        parent::__construct();
        $this->accounts = new ArrayCollection();
    }

    public function getAccounts(): Collection
    {
        return $this->accounts;
    }

    public function setAccounts($accounts): self
    {
        $this->accounts = $accounts;

        return $this;
    }

    public function addAccount(RiotAccount $account): self
    {
        $this->accounts->add($account);

        return $this;
    }

    public function removeAccount(RiotAccount $account): self
    {
        $this->accounts->remove($account);

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

    public function getScore(): ?int
    {
        if (!$this->getAccounts()->count()) {
            return 0;
        }

        return $this->getBestAccount()->getScore();
    }

    public function setScore(int $score): self
    {
        $this->score = $score;

        return $this;
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

    public function getMainAccount(): ?RiotAccount
    {
        $account = $this->getAccounts()->filter(function (RiotAccount $account) {
            return !$account->isSmurf();
        })->first();

        return $account ? $account : null;
    }

    public static function getAvailablePositions(): array
    {
        return [
            self::POSITION_TOP,
            self::POSITION_JUNGLE,
            self::POSITION_MID,
            self::POSITION_ADC,
            self::POSITION_SUPPORT,
        ];
    }
}
