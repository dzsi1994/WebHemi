<?php
/**
 * WebHemi.
 *
 * PHP version 5.6
 *
 * @copyright 2012 - 2016 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link      http://www.gixx-web.com
 */
namespace WebHemi\Data\Entity\User;

use WebHemi\Data\Entity\DataEntityInterface;

/**
 * Class UserMetaEntity.
 */
class UserMetaEntity implements DataEntityInterface
{
    /** @var int */
    private $userMetaId;
    /** @var int */
    private $userId;
    /** @var string */
    private $metaKey;
    /** @var string */
    private $metaData;

    /**
     * Gets the value of the entity identifier.
     *
     * @return int
     */
    public function getKeyData()
    {
        return $this->userMetaId;
    }

    /**
     * @param int $userMetaId
     *
     * @return UserMetaEntity
     */
    public function setUserMetaId($userMetaId)
    {
        $this->userMetaId = $userMetaId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserMetaId()
    {
        return $this->userMetaId;
    }

    /**
     * @param int $userId
     *
     * @return UserMetaEntity
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $metaKey
     *
     * @return UserMetaEntity
     */
    public function setMetaKey($metaKey)
    {
        $this->metaKey = $metaKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKey()
    {
        return $this->metaKey;
    }

    /**
     * @param mixed $metaData
     *
     * @return UserMetaEntity
     */
    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaData()
    {
        return $this->metaData;
    }
}