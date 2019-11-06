<?php

namespace App\Entity\Core\Region;

use App\Entity\Core\Document\RegionLogo;
use App\Entity\Core\Identity\Identity;
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

/**
 * @ORM\Table(name="region__region")
 * @ORM\Entity
 */
class Region
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
     *     "league.get_regions",
     *     "league.get_region",
     *     "league.get_players",
     *     "league.get_player",
     *     "get_staff",
     *     "get_staffs",
     *     "get_teams",
     *     "get_team",
     * })
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     *
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_regions",
     *     "league.get_region",
     *     "league.get_players",
     *     "league.get_player",
     *     "get_staff",
     *     "get_staffs",
     *     "get_teams",
     *     "get_team",
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
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "league.get_regions",
     *     "league.get_region",
     *     "league.get_players",
     *     "league.get_player",
     *     "get_staff",
     *     "get_staffs",
     *     "get_teams",
     *     "get_team",
     * })
     */
    protected $shorthand;

    /**
     * @var array
     * @ORM\Column(type="array", nullable=true)
     * @Serializer\Type("array")
     * @Serializer\Groups({
     *     "league.get_regions",
     *     "league.get_region",
     * })
     */
    protected $countries;

    /**
     * @var ArrayCollection|Identity[]
     * @ORM\ManyToMany(targetEntity="Identity", mappedBy="regions")
     * @Serializer\Type("ArrayCollection<App\Entity\Core\Identity\Identity>")
     */
    protected $identities;

    /**
     * @var ArrayCollection|Team[]
     * @ORM\OneToMany(targetEntity="App\Entity\Core\Team\Team", mappedBy="region")
     * @Serializer\Type("ArrayCollection<App\Entity\Core\Team\Team>")
     */
    protected $teams;

    /**
     * @var RegionLogo
     * @ORM\OneToOne(targetEntity="App\Entity\Core\Document\RegionLogo", mappedBy="region", cascade={"remove"})
     * @Serializer\Type("App\Entity\Core\Document\RegionLogo")
     * @Serializer\Groups({
     *     "league.get_regions",
     *     "league.get_region",
     *     "league.get_players",
     *     "league.get_player",
     *     "get_teams",
     *     "get_team",
     *     "get_staff",
     *     "get_staffs",
     * })
     */
    protected $logo;

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
        $this->teams = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->countries = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUuid(): UuidInterface
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

    public function getShorthand(): ?string
    {
        return $this->shorthand;
    }

    public function setShorthand(string $shorthand): self
    {
        $this->shorthand = $shorthand;

        return $this;
    }

    public function getCountries(): ?array
    {
        return $this->countries;
    }

    public function setCountries(array $countries): self
    {
        $this->countries = $countries;

        return $this;
    }

    public function getIdentities(): Collection
    {
        return $this->identities;
    }

    public function setIdentities($identities): self
    {
        $this->identities = $identities;

        return $this;
    }

    public function addIdentity(Identity $player): self
    {
        $this->identities->add($player);

        return $this;
    }

    public function removeIdentity(Identity $player): self
    {
        $this->identities->remove($player);

        return $this;
    }

    public function getTeams(): Collection
    {
        return $this->teams;
    }

    public function setTeams($teams): self
    {
        $this->teams = $teams;

        return $this;
    }

    public function addTeam(Team $team): self
    {
        $this->teams->add($team);

        return $this;
    }

    public function removeTeam(Team $team): self
    {
        $this->teams->remove($team);

        return $this;
    }

    public function getLogo(): ?RegionLogo
    {
        return $this->logo;
    }

    public function setLogo(RegionLogo $logo): self
    {
        $this->logo = $logo;

        return $this;
    }
}
