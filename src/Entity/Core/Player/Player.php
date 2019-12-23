<?php

namespace App\Entity\Core\Player;

use App\Entity\Core\Region\Region;
use App\Entity\Core\Team\Member;
use App\Entity\Core\Team\Team;
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
 * @ORM\Table(name="player__player")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=75)
 * @ORM\DiscriminatorMap({
 *     "league_player" = "App\Entity\LeagueOfLegends\Player\Player"
 * })
 * @ORM\Entity(repositoryClass="App\Repository\Core\PlayerRepository")
 */
abstract class Player
{
    use StringUuidTrait;

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Exclude
     */
    protected $id;

    /**
     * @var UuidInterface
     * @ORM\Column(name="uuid", type="uuid", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_players",
     *     "league.get_player",
     *     "league.get_riot_account",
     *     "get_team_members",
     *     "get_member",
     * })
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_players",
     *     "league.get_player",
     *     "league.put_player",
     *     "league.get_riot_account",
     *     "get_team_members",
     *     "get_member",
     * })
     * @Assert\NotNull(groups={"league.post_player"})
     */
    protected $name;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_players",
     *     "league.get_player",
     *     "league.put_player",
     *     "get_team_members",
     *     "get_member",
     * })
     */
    protected $country;

    /**
     * @var ArrayCollection|Member[]
     * @ORM\OneToMany(targetEntity="App\Entity\Core\Team\Member", mappedBy="player")
     * @ORM\OrderBy({"joinDate"="DESC"})
     * @Serializer\Type("App\Entity\Core\Team\Member")
     * @Serializer\Groups({
     *     "league.get_players",
     *     "league.get_player",
     * })
     */
    protected $memberships;

    /**
     * @var SocialMedia
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Player\SocialMedia", mappedBy="owner", cascade={"persist", "remove"})
     * @Serializer\Type("App\Entity\Core\Player\SocialMedia")
     */
    protected $socialMedia;

    /**
     * @var ArrayCollection|Region[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Core\Region\Region", inversedBy="players")
     * @Serializer\Type("App\Entity\Core\Region\Region")
     * @Serializer\Groups({
     *     "league.get_players",
     *     "league.get_player",
     *     "league.put_player",
     * })
     */
    private $regions;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Serializer\Type("DateTime")
     */
    protected $createdAt;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     * @Serializer\Type("DateTime")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->socialMedia = new SocialMedia($this);
        $this->regions = new ArrayCollection();
        $this->memberships = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry($country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getSocialMedia(): SocialMedia
    {
        return $this->socialMedia;
    }

    public function setSocialMedia(SocialMedia $socialMedia): self
    {
        $this->socialMedia = $socialMedia;

        return $this;
    }

    public function getRegions(): ?Collection
    {
        return $this->regions;
    }

    public function setRegions($regions): self
    {
        $this->regions = $regions;

        return $this;
    }

    public function addRegion(Region $region): self
    {
        $this->regions->add($region);
        $region->addPlayer($this);

        return $this;
    }

    public function removeRegion(Region $region): self
    {
        $this->regions->remove($region);
        $region->removePlayer($this);

        return $this;
    }

    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    public function addMemberships(Member $member): self
    {
        $this->memberships->add($member);

        return $this;
    }

    public function removeMemberships(Member $member): self
    {
        $this->memberships->removeElement($member);

        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getCurrentTeam(): ?Team
    {
        /** @var Member $membership */
        $membership = $this->memberships->filter(function (Member $membership) {
            return (bool) $membership->getLeaveDate();
        })->first();

        return $membership ? $membership->getTeam() : null;
    }
}
