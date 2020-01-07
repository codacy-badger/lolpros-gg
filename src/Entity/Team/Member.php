<?php

namespace App\Entity\Team;

use App\Entity\Profile\Profile;
use App\Entity\StringUuidTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="team__members")
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 */
class Member
{
    use StringUuidTrait;
    const MEMBER_STAFF = 'staff';
    const MEMBER_PLAYER = 'player';
    const MEMBER_SUB = 'sub';

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
     *     "get_profiles",
     *     "get_profile",
     *     "get_teams",
     *     "get_team",
     * })
     */
    protected $uuid;

    /**
     * @var Profile
     * @ORM\ManyToOne(targetEntity="App\Entity\Profile\Profile", inversedBy="memberships")
     * @Serializer\Type("App\Entity\Profile\Profile")
     * @Serializer\Groups({
     * })
     */
    protected $profile;

    /**
     * @var Team
     * @ORM\ManyToOne(targetEntity="App\Entity\Team\Team", inversedBy="members")
     * @Serializer\Type("App\Entity\Team\Team")
     * @Serializer\Groups({
     * })
     */
    protected $team;

    /**
     * @var DateTime
     * @ORM\Column(name="join_date", type="datetime", nullable=true)
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({
     *     "get_teams",
     *     "get_team",
     * })
     */
    protected $joinDate;

    /**
     * @var DateTime
     * @ORM\Column(name="leave_date", type="datetime", nullable=true)
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({
     *     "get_teams",
     *     "get_team",
     * })
     */
    protected $leaveDate;

    /**
     * @var string
     * @ORM\Column(name="role", type="string")
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_member",
     *     "get_player_members",
     *     "get_team_members",
     *     "get_teams",
     * })
     */
    protected $role;

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
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getTeam(): ?Team
    {
        return $this->team;
    }

    public function setTeam(Team $team): self
    {
        $this->team = $team;

        return $this;
    }

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getJoinDate(): ?DateTime
    {
        return $this->joinDate;
    }

    public function setJoinDate(DateTime $joinDate): self
    {
        $this->joinDate = $joinDate;

        return $this;
    }

    public function getLeaveDate(): ?DateTime
    {
        return $this->leaveDate;
    }

    public function setLeaveDate(?DateTime $leaveDate): self
    {
        $this->leaveDate = $leaveDate;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function isCurrent(): bool
    {
        return !$this->leaveDate;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public static function getAvailableRoles(): array
    {
        return [
            self::MEMBER_STAFF,
            self::MEMBER_PLAYER,
            self::MEMBER_SUB,
        ];
    }
}
