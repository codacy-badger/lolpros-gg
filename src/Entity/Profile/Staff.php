<?php

namespace App\Entity\Profile;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="profile__staff")
 * @ORM\Entity
 */
class Staff
{
    const POSITION_COACH = '10_coach';
    const POSITION_ANALYST = '20_analyst';
    const POSITION_MANAGER = '30_manager';

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
     * @Assert\NotNull()
     * @Assert\Choice(callback="getAvailablePosition", strict=true)
     * @Serializer\Groups({
     *     "get_profiles",
     *     "get_profile",
     *     "get_team_members",
     * })
     */
    protected $position;

    /**
     * @var Profile
     * @ORM\OneToOne(targetEntity="App\Entity\Profile\Profile", inversedBy="staff")
     * @Serializer\Type("App\Entity\Profile\Profile")
     */
    protected $profile;

    public function getPosition(): string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
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

    public function getUuidAsString(): string
    {
        return $this->getProfile()->getUuidAsString();
    }

    public static function getAvailablePositions(): array
    {
        return [
            self::POSITION_COACH,
            self::POSITION_ANALYST,
            self::POSITION_MANAGER,
        ];
    }
}
