<?php

namespace App\Entity\Core\Team;

use App\Entity\Core\Identity\Identity;
use App\Entity\SelfReferencedEntityTrait;
use App\Entity\StringUuidTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="team__members")
 * @ORM\Entity
 */
class Member
{
    use SelfReferencedEntityTrait;
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
     *     "league.get_player",
     *     "league.get_players",
     *     "get_identity_memberships",
     *     "get_teams",
     *     "get_team",
     *     "get_team_members",
     *     "get_staff",
     *     "get_staffs",
     * })
     */
    protected $uuid;

    /**
     * @var Identity
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Identity\Identity", inversedBy="memberships")
     * @Serializer\Type("App\Entity\Core\Identity\Identity")
     * @Serializer\Groups({
     *     "get_team_members",
     * })
     */
    protected $identity;

    /**
     * @var Team
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\Team\Team", inversedBy="members")
     * @Serializer\Type("App\Entity\Core\Team\Team")
     * @Serializer\Groups({
     *     "get_player_members",
     *     "get_team_members",
     *     "get_identity_memberships",
     * })
     */
    protected $team;

    /**
     * @var DateTime
     * @ORM\Column(name="join_date", type="datetime", nullable=true)
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     *     "get_team_members",
     *     "get_teams",
     * })
     */
    protected $joinDate;

    /**
     * @var DateTime
     * @ORM\Column(name="leave_date", type="datetime", nullable=true)
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({
     *     "league.get_players",
     *     "get_identity_memberships",
     *     "get_team_members",
     *     "get_teams",
     *     "get_staffs",
     * })
     */
    protected $leaveDate;

    /**
     * @var string
     * @ORM\Column(name="role", type="string")
     * @Serializer\Type("string")
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

    public function getIdentity(): ?Identity
    {
        return $this->identity;
    }

    public function setIdentity(Identity $identity): self
    {
        $this->identity = $identity;

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
