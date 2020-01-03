<?php

namespace App\Entity\User;

use App\Entity\Report\AdminLog;
use App\Entity\StringUuidTrait;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    use StringUuidTrait;

    const ROLE_DEFAULT = 'ROLE_USER';
    const ROLE_ADMIN = 'ROLE_ADMIN';
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var UuidInterface
     * @ORM\Column(type="uuid", nullable=false, unique=true)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false, unique=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_admin_logs",
     * })
     */
    protected $username;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     */
    protected $email;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $salt;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * @var array
     * @ORM\Column(type="array")
     */
    protected $roles;

    /**
     * @var Collection|AdminLog
     * @ORM\OneToMany(targetEntity="App\Entity\Report\AdminLog", mappedBy="user")
     */
    protected $edits;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $discordId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $twitchId;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $twitterToken;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    protected $twitterSecret;

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
        $this->roles = [];
        $this->uuid = Uuid::uuid4();
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function __toString(): string
    {
        return (string) $this->getUsername();
    }

    public function addRole($role): self
    {
        $role = strtoupper($role);
        if ($role === static::ROLE_DEFAULT) {
            return $this;
        }

        if (!in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function serialize()
    {
        return serialize([
            $this->uuid,
            $this->username,
            $this->email,
            $this->password,
            $this->salt,
        ]);
    }

    public function getUuid(): ?UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid(UuidInterface $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;

        // we need to make sure to have at least one role
        $roles[] = static::ROLE_DEFAULT;

        return array_unique($roles);
    }

    public function getDiscordId()
    {
        return $this->discordId;
    }

    public function setDiscordId($discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    public function getTwitchId(): string
    {
        return $this->twitchId;
    }

    public function setTwitchId($twitchId): self
    {
        $this->twitchId = $twitchId;

        return $this;
    }

    public function getTwitterToken(): string
    {
        return $this->twitterToken;
    }

    public function setTwitterToken($twitterToken): self
    {
        $this->twitterToken = $twitterToken;

        return $this;
    }

    public function getTwitterSecret(): string
    {
        return $this->twitterSecret;
    }

    public function setTwitterSecret($twitterSecret): self
    {
        $this->twitterSecret = $twitterSecret;

        return $this;
    }

    public function getSalt()
    {
        return null;
    }

    public function eraseCredentials()
    {
        return null;
    }
}
