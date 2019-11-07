<?php

namespace App\Entity\Core\Identity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="player__social_media")
 * @ORM\Entity
 */
class SocialMedia
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @Serializer\Exclude
     */
    protected $id;

    /**
     * @var Identity
     * @ORM\OneToOne(targetEntity="Identity", inversedBy="socialMedia")
     * @Serializer\Type("App\Entity\Core\Identity\Identity")
     */
    protected $owner;

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
     * @Serializer\Type("DateTime")
     */
    protected $updatedAt;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_social_medias",
     *     "put_identity_social_medias",
     * })
     */
    protected $twitter;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_social_medias",
     *     "put_identity_social_medias",
     * })
     */
    protected $facebook;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_social_medias",
     *     "put_identity_social_medias",
     * })
     */
    protected $twitch;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_social_medias",
     *     "put_identity_social_medias",
     * })
     */
    protected $discord;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_social_medias",
     *     "put_identity_social_medias",
     * })
     */
    protected $leaguepedia;

    public function __construct(Identity $identity)
    {
        $this->owner = $identity;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getOwner(): Identity
    {
        return $this->owner;
    }

    public function setOwner(Identity $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function getTwitter(): ?string
    {
        return $this->twitter;
    }

    public function setTwitter(?string $twitter): self
    {
        $this->twitter = $twitter;

        return $this;
    }

    public function getFacebook(): ?string
    {
        return $this->facebook;
    }

    public function setFacebook(?string $facebook): self
    {
        $this->facebook = $facebook;

        return $this;
    }

    public function getTwitch(): ?string
    {
        return $this->twitch;
    }

    public function setTwitch(?string $twitch): self
    {
        $this->twitch = $twitch;

        return $this;
    }

    public function getDiscord(): ?string
    {
        return $this->discord;
    }

    public function setDiscord(?string $discord): self
    {
        $this->discord = $discord;

        return $this;
    }

    public function getLeaguepedia(): ?string
    {
        return $this->leaguepedia;
    }

    public function setLeaguepedia(?string $leaguepedia): self
    {
        $this->leaguepedia = $leaguepedia;

        return $this;
    }
}
