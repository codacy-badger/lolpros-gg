<?php

namespace App\Entity\Report;

use App\Entity\LeagueOfLegends\LeaguePlayer;
use App\Entity\StringUuidTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class AddRequest
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
     *     "get_add_requests",
     *     "get_add_request",
     * })
     */
    protected $uuid;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Serializer\Groups({
     *     "get_add_requests",
     *     "get_add_request",
     * })
     */
    protected $createdAt;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_add_requests",
     *     "get_add_request",
     * })
     */
    protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_add_request",
     * })
     */
    protected $country;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_add_request",
     * })
     * @Assert\Choice(callback="getAvailablePositions", strict=true)
     */
    protected $position;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_add_request",
     * })
     */
    protected $twitter;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_add_request",
     * })
     */
    protected $twitch;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_add_request",
     * })
     */
    protected $comment;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_add_request",
     * })
     */
    protected $summonerName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_add_request",
     * })
     */
    protected $summonerId;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
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

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getTwitch(): ?string
    {
        return $this->twitch;
    }

    public function setTwitch(string $twitch): self
    {
        $this->twitch = $twitch;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getSummonerName(): ?string
    {
        return $this->summonerName;
    }

    public function setSummonerName(string $summonerName): self
    {
        $this->summonerName = $summonerName;

        return $this;
    }

    public function getSummonerId(): ?string
    {
        return $this->summonerId;
    }

    public function setSummonerId(string $summonerId): self
    {
        $this->summonerId = $summonerId;

        return $this;
    }

    public static function getAvailablePositions(): array
    {
        return Player::getAvailablePositions();
    }
}
