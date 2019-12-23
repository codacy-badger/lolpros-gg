<?php

namespace App\Entity\Core\Team;

use App\Entity\Core\Document\TeamLogo;
use App\Entity\Core\Region\Region;
use App\Entity\StringUuidTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use InvalidArgumentException;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="team__team")
 * @ORM\Entity(repositoryClass="App\Repository\Core\TeamRepository")
 */
class Team
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
     *     "get_teams",
     *     "get_team",
     *     "put_team",
     *     "get_player_members",
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
     *     "get_teams",
     *     "get_team",
     *     "put_team",
     *     "get_player_members",
     *     "get_team_members",
     *     "get_member",
     * })
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
     * @var TeamLogo
     * @ORM\OneToOne(targetEntity="\App\Entity\Core\Document\TeamLogo", mappedBy="team", cascade={"remove"})
     * @Serializer\Type("App\Entity\Core\Document\TeamLogo")
     * @Serializer\Groups({
     *     "get_player_members",
     *     "get_teams",
     *     "get_team",
     * })
     */
    protected $logo;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_team",
     *     "get_teams",
     *     "put_team",
     * })
     */
    protected $tag;

    /**
     * @var DateTime
     * @ORM\Column(name="creation_date", type="datetime", nullable=true)
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({
     *     "get_team",
     *     "put_team",
     * })
     */
    protected $creationDate;

    /**
     * @var DateTime
     * @ORM\Column(name="disband_date", type="datetime", nullable=true)
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({
     *     "get_team",
     *     "put_team",
     * })
     */
    protected $disbandDate;

    /**
     * @var SocialMedia
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Team\SocialMedia", mappedBy="owner", cascade={"persist", "remove"})
     * @Serializer\Type("App\Entity\Core\Team\SocialMedia")
     */
    protected $socialMedia;

    /**
     * @var Region
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Region\Region", inversedBy="teams")
     * @Serializer\Type("App\Entity\Core\Region\Region")
     * @Serializer\Groups({
     *     "get_teams",
     *     "get_team",
     *     "put_team",
     * })
     */
    protected $region;

    /**
     * @var ArrayCollection|Member[]
     * @ORM\OneToMany(targetEntity="App\Entity\Core\Team\Member", mappedBy="team")
     * @ORM\OrderBy({"leaveDate"="ASC", "joinDate"="DESC"})
     * @Serializer\Type("ArrayCollection<App\Entity\Core\Team\Member>")
     * @Serializer\Groups({
     *     "get_teams",
     *     "get_team",
     * })
     */
    protected $members;

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
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
        $this->socialMedia = new SocialMedia($this);
        $this->members = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    public function getCreationDate(): ?DateTime
    {
        return $this->creationDate;
    }

    public function setCreationDate(?DateTime $creationDate): self
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    public function getDisbandDate(): ?DateTime
    {
        return $this->disbandDate;
    }

    public function setDisbandDate(?DateTime $disbandDate): self
    {
        $this->disbandDate = $disbandDate;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getLogo(): ?TeamLogo
    {
        return $this->logo;
    }

    public function setLogo(?TeamLogo $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
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

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Member $member): self
    {
        $this->members->add($member);

        return $this;
    }

    public function removeMember(Member $member): self
    {
        $this->members->removeElement($member);

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Returns members that belonged to the team at the same time as the member.
     */
    public function getSharedMemberships(Member $current): ArrayCollection
    {
        if ($current->getTeam()->getUuidAsString() !== $this->getUuidAsString()) {
            throw new InvalidArgumentException(sprintf("Member %s doesn't belong to the team %s", $current->getUuidAsString(), $this->getUuidAsString()));
        }

        return $this->members->filter(function (Member $member) use ($current) {
            //Member is current
            if (!$member->getLeaveDate()) {
                return false;
            }
            //Member left before current joined
            if ($this->isAfter($current->getJoinDate(), $member->getLeaveDate())) {
                return false;
            }
            //Member left when current joined
            if ($member->getLeaveDate() === $current->getJoinDate()) {
                return false;
            }

            return true;
        });
    }

    //Returns whether the second date is after the first
    private function isAfter(DateTime $first, DateTime $second)
    {
        if ($first->format('o') < $second->format('o')) {
            return false;
        }

        if ($first->format('o') === $second->format('o') && $first->format('n') < $second->format('n')) {
            return false;
        }

        if ($first->format('o') === $second->format('o') &&
            $first->format('n') === $second->format('n') &&
            $first->format('j') < $second->format('j')) {
            return false;
        }

        return true;
    }

    public function getMembersBetweenDates(DateTime $begin, DateTime $end = null): ?ArrayCollection
    {
        if (!$end) {
            $end = new DateTime();
        }

        return $this->members->filter(function (Member $membership) use ($begin, $end) {
            if ($this->isAfter($membership->getJoinDate(), $end)) {
                return false;
            }

            if ($membership->getLeaveDate() && !$this->isAfter($membership->getLeaveDate(), $begin)) {
                return false;
            }

            if ($membership->getJoinDate() == $end || $membership->getLeaveDate() == $begin) {
                return false;
            }

            return true;
        });
    }
}
