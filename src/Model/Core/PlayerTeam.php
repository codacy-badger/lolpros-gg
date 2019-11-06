<?php

namespace App\Model\Core;

use DateTime;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\UuidInterface;

class PlayerTeam
{
    /**
     * @var UuidInterface
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $uuid;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $name;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $displayName;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $slug;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $tag;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $logo;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $active;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $joinDate;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $leaveDate;

    /**
     * @var string
     * @Serializer\Type("string")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $role;

    /**
     * @var bool
     * @Serializer\Type("boolean")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $current;

    /**
     * @var array
     * @Serializer\Type("array")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $members;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $creationDate;

    /**
     * @var DateTime
     * @Serializer\Type("DateTime")
     * @Serializer\Groups({
     *     "get_identity_memberships",
     * })
     */
    public $disbandDate;
}
