<?php

namespace App\Entity\Core\Document;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="document")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=75)
 * @ORM\DiscriminatorMap({
 *     "document__team_logo" = "App\Entity\Core\Document\TeamLogo",
 *     "document__region_logo" = "App\Entity\Core\Document\RegionLogo"
 * })
 */
abstract class Document
{
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
     * @ORM\Column(name="uuid", type="uuid")
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_region",
     *     "get_team",
     * })
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(name="public_id", type="string", length=255)
     * @Serializer\Type("string")
     */
    protected $publicId;

    /**
     * @var string
     * @ORM\Column(name="version", type="string", length=255)
     * @Serializer\Type("string")
     */
    protected $version;

    /**
     * @var string
     * @ORM\Column(name="url", type="string", length=255)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_regions",
     *     "league.get_region",
     *     "league.get_players",
     *     "league.get_player",
     *     "get_staff",
     *     "get_staffs",
     *     "get_player_members",
     *     "get_teams",
     *     "get_team",
     * })
     */
    protected $url;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function getPublicId(): string
    {
        return $this->publicId;
    }

    public function setPublicId(string $publicId): self
    {
        $this->publicId = $publicId;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
