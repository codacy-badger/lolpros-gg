<?php

namespace App\Entity\Core\Player;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="player__player")
 */
class Staff extends Player
{
    const ROLE_COACH = '10_coach';
    const ROLE_MANAGER = '20_manager';
    const ROLE_ANALYST = '30_analyst';
    const ROLE_OTHER = '99_other';

    /**
     * @var string
     * @ORM\Column(type="string")
     * @Serializer\Type("string")
     * @Assert\NotNull(groups={"post_staff"})
     * @Assert\Choice(callback="getAvailableRoles", strict=true)
     * @Serializer\Groups({
     *     "get_staffs",
     *     "get_staff",
     *     "put_staff",
     * })
     */
    protected $role;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_staffs",
     *     "get_staff",
     *     "put_staff",
     * })
     */
    protected $roleName;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getRoleName(): ?string
    {
        return $this->roleName;
    }

    public function setRoleName(string $roleName): void
    {
        $this->roleName = $roleName;
    }

    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_COACH,
            self::ROLE_MANAGER,
            self::ROLE_ANALYST,
            self::ROLE_OTHER,
        ];
    }
}
