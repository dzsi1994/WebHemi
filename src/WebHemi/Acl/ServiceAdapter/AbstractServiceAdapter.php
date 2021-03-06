<?php
/**
 * WebHemi.
 *
 * PHP version 7.1
 *
 * @copyright 2012 - 2018 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link http://www.gixx-web.com
 */
declare(strict_types = 1);

namespace WebHemi\Acl\ServiceAdapter;

use WebHemi\Acl;
use WebHemi\Data\Entity\PolicyEntity;
use WebHemi\Data\Entity\ResourceEntity;
use WebHemi\Data\Entity\ApplicationEntity;
use WebHemi\Data\Entity\UserEntity;
use WebHemi\Data\Storage\PolicyStorage;
use WebHemi\Data\Storage\UserStorage;
use WebHemi\Environment\ServiceInterface as EnvironmentInterface;

/**
 * Class AbstractServiceAdapter.
 */
abstract class AbstractServiceAdapter implements Acl\ServiceInterface
{
    /**
     * @var EnvironmentInterface
     */
    protected $environment;

    /**
     * @var UserStorage
     */
    protected $userStorage;

    /**
     * @var PolicyStorage
     */
    protected $policyStorage;

    /**
     * ServiceAdapter constructor.
     *
     * @param EnvironmentInterface  $environment
     * @param UserStorage           $userStorage
     * @param PolicyStorage         $policyStorage
     */
    public function __construct(
        EnvironmentInterface $environment,
        UserStorage $userStorage,
        PolicyStorage $policyStorage
    ) {
        $this->environment = $environment;
        $this->userStorage = $userStorage;
        $this->policyStorage = $policyStorage;
    }

    /**
     * Checks if a User can access to a Resource in an Application
     *
     * @param  UserEntity             $userEntity
     * @param  ResourceEntity|null    $resourceEntity
     * @param  ApplicationEntity|null $applicationEntity
     * @return bool
     */
    abstract public function isAllowed(
        UserEntity $userEntity,
        ? ResourceEntity $resourceEntity = null,
        ? ApplicationEntity $applicationEntity = null
    ) : bool;

    /**
     * Checks a given policy against a resource, application and method.
     *
     * The user has access when the user or the user's group has a policy which:
     *  - is connected to the current resource OR any resource AND
     *  - is connected to the current application OR any application AND
     *  - allows the current request method.
     *
     * @param  PolicyEntity           $policyEntity
     * @param  null|ResourceEntity    $resourceEntity
     * @param  null|ApplicationEntity $applicationEntity
     * @param  null|string            $method
     * @return bool
     */
    protected function isPolicyAllowed(
        PolicyEntity $policyEntity,
        ? ResourceEntity $resourceEntity = null,
        ? ApplicationEntity $applicationEntity = null,
        ? string $method = null
    ) : bool {
        return $this->isResourceAllowed($policyEntity, $resourceEntity)
            && $this->isApplicationAllowed($policyEntity, $applicationEntity)
            && $this->isRequestMethodAllowed($policyEntity, $method);
    }

    /**
     * Checks whether the given resource is allowed for the given policy.
     *
     * @param  PolicyEntity        $policyEntity
     * @param  ResourceEntity|null $resourceEntity
     * @return bool
     */
    private function isResourceAllowed(
        PolicyEntity $policyEntity,
        ? ResourceEntity $resourceEntity = null
    ) : bool {
        $policyResourceId = $policyEntity->getResourceId();
        $resourceId = $resourceEntity ? $resourceEntity->getResourceId() : null;

        return is_null($policyResourceId) || $policyResourceId === $resourceId;
    }

    /**
     * Checks whether the given application is allowed for the given policy.
     *
     * @param  PolicyEntity                $policyEntity
     * @param  null|ApplicationEntity|null $applicationEntity
     * @return bool
     */
    private function isApplicationAllowed(
        PolicyEntity $policyEntity,
        ? ApplicationEntity $applicationEntity = null
    ) : bool {
        $policyApplicationId = $policyEntity->getApplicationId();
        $applicationId = $applicationEntity ? $applicationEntity->getApplicationId() : null;

        return is_null($policyApplicationId) || $policyApplicationId === $applicationId;
    }

    /**
     * Checks whether the request method is allowed for the given policy.
     *
     * @param  PolicyEntity $policyEntity
     * @param  null|string  $method
     * @return bool
     */
    private function isRequestMethodAllowed(PolicyEntity $policyEntity, ? string $method = null) : bool
    {
        $policyRequestMethod = $policyEntity->getMethod();
        $requestMethod = $method ?? $this->environment->getRequestMethod();
        $allowRequestMethod = is_null($policyRequestMethod) || $policyRequestMethod === $requestMethod;

        return $allowRequestMethod;
    }
}
