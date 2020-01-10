<?php

namespace App\Entity\Profile;

use App\Entity\LeagueOfLegends\Player;
use App\Entity\Region\Region;
use App\Entity\StringUuidTrait;
use App\Entity\Team\Member;
use App\Entity\Team\Team;
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
 * @ORM\Table(name="profile__profile")
 * @ORM\Entity(repositoryClass="App\Repository\ProfileRepository")
 */
class Profile
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
     *     "get_profiles",
     *     "get_profile",
     *     "get_team_members",
     * })
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     *     "get_team_members",
     * })
     * @Assert\NotNull(groups={"league.post_player"})
     */
    protected $name;

    /**
     * @var string
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     *     "get_team_members",
     * })
     */
    protected $slug;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     *     "get_team_members",
     * })
     */
    protected $country;

    /**
     * @var ArrayCollection|Member[]
     * @ORM\OneToMany(targetEntity="App\Entity\Team\Member", mappedBy="profile")
     * @ORM\OrderBy({"joinDate"="DESC"})
     * @Serializer\Type("ArrayCollection<App\Entity\Team\Member>")
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     * })
     */
    protected $memberships;

    /**
     * @var SocialMedia
     * @ORM\OneToOne(targetEntity="App\Entity\Profile\SocialMedia", mappedBy="owner", cascade={"persist", "remove"})
     * @Serializer\Type("App\Entity\Profile\SocialMedia")
     * @Serializer\Groups({
     *     "get_profile",
     * })
     */
    protected $socialMedia;

    /**
     * @var ArrayCollection|Region[]
     * @ORM\ManyToMany(targetEntity="App\Entity\Region\Region", inversedBy="profiles")
     * @ORM\JoinTable(name="region__profile")
     * @Serializer\Type("App\Entity\Region\Region")
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     * })
     */
    private $regions;

    /**
     * @var Staff
     * @ORM\OneToOne(targetEntity="App\Entity\Profile\Staff", mappedBy="profile")
     * @Serializer\Type("App\Entity\Profile\Staff")
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     *     "get_team_members",
     * })
     */
    protected $staff;

    /**
     * @var Player
     * @ORM\OneToOne(targetEntity="App\Entity\LeagueOfLegends\Player", mappedBy="profile")
     * @Serializer\Type("App\Entity\LeagueOfLegends\Player")
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     *     "get_team_members",
     * })
     */
    protected $leaguePlayer;

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

    public function getStaff(): ?Staff
    {
        return $this->staff;
    }

    public function setStaff(Staff $staff): self
    {
        $this->staff = $staff;

        return $this;
    }

    public function getLeaguePlayer(): ?Player
    {
        return $this->leaguePlayer;
    }

    public function setLeaguePlayer(Player $leaguePlayer): self
    {
        $this->leaguePlayer = $leaguePlayer;

        return $this;
    }

    /**
     * @Serializer\VirtualProperty()
     */
    public function getCurrentTeam(): ?Team
    {
        /** @var Member $membership */
        $membership = $this->memberships->filter(function (Member $membership) {
            return !$membership->getLeaveDate();
        })->first();

        return $membership ? $membership->getTeam() : null;
    }

    public function getCurrentMemberships(): ArrayCollection
    {
        return $this->memberships->filter(function (Member $membership) {
            return !$membership->getLeaveDate();
        });
    }

    public function getPreviousMemberships(): ArrayCollection
    {
        return $this->memberships->filter(function (Member $membership) {
            return (bool) $membership->getLeaveDate();
        });
    }
}
