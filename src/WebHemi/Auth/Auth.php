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
namespace WebHemi\Auth;

use Exception;
use WebHemi\Data\Storage\User\UserStorage;
use WebHemi\Data\Entity\User\UserEntity;
use WebHemi\Adapter\Auth\AbstractAuthAdapter;

/**
 * Class Auth
 *
 * @method UserStorage getDataStorage()
 *
 * @codeCoverageIgnore - unfinished code
 */
class Auth extends AbstractAuthAdapter
{
    /**
     * Authenticates the user.
     *
     * @return Result
     */
    public function authenticate()
    {
        // TODO implement
        $result = $this->getAuthResult();
        $user = $this->getDataStorage()->getUserById(1);
        $result->setIdentity($user);
        $result->setCode(Result::SUCCESS);
        return $result;
    }

    /**
     * Gets the authenticated user's entity.
     *
     * @throws Exception
     * @return null|string|UserEntity
     */
    public function getIdentity()
    {
        $identity = parent::getIdentity();
        // TODO implement

        if (!$identity instanceof UserEntity) {
            $userName = 'admin';

            /** @var UserEntity $userEntity */
            $userEntity = $this->getDataStorage()->getUserByUserName($userName);

            if (!$userEntity) {
                $identity = $userName;
            } else {
                return $userEntity;
            }
        }

        return $identity;
    }
}