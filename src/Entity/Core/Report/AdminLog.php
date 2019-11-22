<?php

namespace App\Entity\Core\Report;

use App\Entity\StringUuidTrait;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 */
class AdminLog
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
     *     "get_admin_logs",
     * })
     */
    protected $uuid;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Serializer\Type("DateTime")
     * @Serializer\Groups({
     *     "get_admin_logs",
     * })
     */
    protected $createdAt;

    /**
     * @var UserInterface
     * @ORM\ManyToOne(targetEntity="App\Entity\Core\User\User", inversedBy="edits")
     * @Serializer\Type("App\Entity\Core\User\User")
     * @Serializer\Groups({
     *     "get_admin_logs",
     * })
     */
    protected $user;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_admin_logs",
     * })
     */
    protected $type;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     */
    protected $entityUuid;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_admin_logs",
     * })
     */
    protected $entityName;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     */
    protected $linkedUuid;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_admin_logs",
     * })
     */
    protected $linkedName;

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

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getEntityUuid(): string
    {
        return $this->entityUuid;
    }

    public function setEntityUuid(string $entityUuid): self
    {
        $this->entityUuid = $entityUuid;

        return $this;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): self
    {
        $this->entityName = $entityName;

        return $this;
    }

    public function getLinkedUuid(): ?string
    {
        return $this->linkedUuid;
    }

    public function setLinkedUuid(?string $linkedUuid): self
    {
        $this->linkedUuid = $linkedUuid;

        return $this;
    }

    public function getLinkedName(): ?string
    {
        return $this->linkedName;
    }

    public function setLinkedName(?string $linkedName): self
    {
        $this->linkedName = $linkedName;

        return $this;
    }
}
