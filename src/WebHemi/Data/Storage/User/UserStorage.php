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
namespace WebHemi\Data\Storage\User;

use DateTime;
use WebHemi\Data\Entity\DataEntityInterface;
use WebHemi\Data\Entity\User\UserEntity;
use WebHemi\Data\Storage\AbstractDataStorage;

/**
 * Class UserStorage.
 */
class UserStorage extends AbstractDataStorage
{
    /** @var string */
    protected $dataGroup = 'webhemi_user';
    /** @var string */
    protected $idKey = 'id_user';
    /** @var string */
    private $userName = 'username';
    /** @var string */
    private $email = 'email';
    /** @var string */
    private $password = 'password';
    /** @var string */
    private $hash = 'hash';
    /** @var string */
    private $isActive = 'is_active';
    /** @var string */
    private $isEnabled = 'is_enabled';
    /** @var string */
    private $dateCreated = 'date_created';
    /** @var string */
    private $dateModified = 'date_modified';

    /**
     * Populates an entity with storage data.
     *
     * @param DataEntityInterface $entity
     * @param array               $data
     */
    protected function populateEntity(DataEntityInterface &$entity, array $data)
    {
        /* @var UserEntity $entity */
        $entity->setUserId($data[$this->idKey])
            ->setUserName($data[$this->userName])
            ->setEmail($data[$this->email])
            ->setPassword($data[$this->password])
            ->setHash($data[$this->hash])
            ->setActive($data[$this->isActive])
            ->setEnabled($data[$this->isEnabled])
            ->setDateCreated(new DateTime($data[$this->dateCreated]))
            ->setDateModified(new DateTime($data[$this->dateModified]));
    }

    /**
     * Returns a User entity identified by (unique) ID.
     *
     * @param int $identifier
     *
     * @return bool|UserEntity
     */
    public function getUserById($identifier)
    {
        $entity = false;
        $data = $this->getDataAdapter()->getData($identifier);

        if (!empty($data)) {
            $entity = $this->createEntity();
            $this->populateEntity($entity, $data);
        }

        return $entity;
    }

    /**
     * Returns a User entity identified by (unique) Email.
     *
     * @param $email
     *
     * @return bool|UserEntity
     */
    public function getUserByEmail($email)
    {
        $entity = false;
        $dataList = $this->getDataAdapter()->getDataSet([$this->email => $email], 1);

        if (!empty($dataList)) {
            $entity = $this->createEntity();
            $this->populateEntity($entity, $dataList[0]);
        }

        return $entity;
    }
}