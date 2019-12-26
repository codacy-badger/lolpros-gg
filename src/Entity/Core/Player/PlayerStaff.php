<?php

namespace App\Entity\Core\Player;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="player__staff")
 * @ORM\Entity
 */
class PlayerStaff
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
     * })
     */
    protected $position;

    /**
     * @var Player
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Player\Player", mappedBy="staff")
     * @Serializer\Type("App\Entity\Core\Player\Player")
     */
    protected $player;

    public static function getAvailablePositions(): array
    {
        return [
            self::POSITION_COACH,
            self::POSITION_ANALYST,
            self::POSITION_MANAGER,
        ];
    }
}
