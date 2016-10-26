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
namespace WebHemi\Data\Coupler\Traits;

use DateTime;
use RuntimeException;
use WebHemi\Data\Entity\User\UserEntity;

/**
 * Class UserEntityTrait.
 */
trait UserEntityTrait
{
    /**
     * Returns a new instance of the required entity.
     *
     * @param string $entityClassName
     * @throws RuntimeException
     * @return UserEntity
     */
    abstract protected function getNewEntityInstance($entityClassName);

    /**
     * Creates a new User Entity instance form the data.
     *
     * @param array $data
     * @return UserEntity
     */
    protected function createUserEntity(array $data)
    {
        $entity = $this->getNewEntityInstance(UserEntity::class);

        $entity->setUserId($data['id_user'])
            ->setUserName($data['username'])
            ->setEmail($data['email'])
            ->setPassword($data['password'])
            ->setHash($data['hash'])
            ->setActive($data['is_active'])
            ->setEnabled($data['is_enabled'])
            ->setDateCreated(new DateTime($data['date_created']))
            ->setDateModified(new DateTime($data['date_modified']));

        return $entity;
    }
}
