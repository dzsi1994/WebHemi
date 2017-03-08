<?php
/**
 * WebHemi.
 *
 * PHP version 7.1
 *
 * @copyright 2012 - 2017 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link      http://www.gixx-web.com
 */
declare(strict_types = 1);

namespace WebHemi\Acl\ServiceAdapter\Base;

use WebHemi\Acl\ServiceInterface;
use WebHemi\Data\Coupler\UserGroupToPolicyCoupler;
use WebHemi\Data\Coupler\UserToGroupCoupler;
use WebHemi\Data\Coupler\UserToPolicyCoupler;
use WebHemi\Data\Entity\AccessManagement\PolicyEntity;
use WebHemi\Data\Entity\AccessManagement\ResourceEntity;
use WebHemi\Data\Entity\ApplicationEntity;
use WebHemi\Data\Entity\User\UserEntity;

/**
 * Class ServiceAdapter.
 */
class ServiceAdapter implements ServiceInterface
{
    /** @var UserToPolicyCoupler */
    private $userToPolicyCoupler;
    /** @var UserToGroupCoupler */
    private $userToGroupCoupler;
    /** @var UserGroupToPolicyCoupler */
    private $userGroupToPolicyCoupler;

    /**
     * ServiceAdapter constructor.
     *
     * @param UserToPolicyCoupler      $userToPolicyCoupler
     * @param UserToGroupCoupler       $userToGroupCoupler
     * @param UserGroupToPolicyCoupler $userGroupToPolicyCoupler
     */
    public function __construct(
        UserToPolicyCoupler $userToPolicyCoupler,
        UserToGroupCoupler $userToGroupCoupler,
        UserGroupToPolicyCoupler $userGroupToPolicyCoupler
    ) {
        $this->userToPolicyCoupler = $userToPolicyCoupler;
        $this->userToGroupCoupler = $userToGroupCoupler;
        $this->userGroupToPolicyCoupler = $userGroupToPolicyCoupler;
    }

    /**
     * Checks if a User can access to a Resource in an Application
     *
     * @param UserEntity             $userEntity
     * @param ResourceEntity|null    $resourceEntity
     * @param ApplicationEntity|null $applicationEntity
     * @return bool
     */
    public function isAllowed(
        UserEntity $userEntity,
        ?ResourceEntity $resourceEntity = null,
        ?ApplicationEntity $applicationEntity = null
    ) : bool {
        // We assume the best case: the user has access
        $allowed = false;

        /** @var array<PolicyEntity> $policies */
        $policies = array_merge($this->getUserPolicies($userEntity), $this->getUserGroupPolicies($userEntity));

        foreach ($policies as $policyEntity) {
            $allowed = $allowed || $this->checkPolicy($policyEntity, $resourceEntity, $applicationEntity);
        }

        return $allowed;
    }

    /**
     * Gets the policies assigned to the user.
     *
     * @param UserEntity $userEntity
     * @return array<PolicyEntity>
     */
    private function getUserPolicies(UserEntity $userEntity) : array
    {
        /** @var array<PolicyEntity> $userPolicies */
        return $this->userToPolicyCoupler->getEntityDependencies($userEntity);
    }

    /**
     * Gets the policies assigned to the group in which the user is.
     *
     * @param UserEntity $userEntity
     * @return array<PolicyEntity>
     */
    private function getUserGroupPolicies(UserEntity $userEntity) : array
    {
        /** @var array<PolicyEntity> $userGroupPolicies */
        $userGroupPolicies = [];
        /** @var array<UserGroupEntity> $userGroups */
        $userGroups = $this->userToGroupCoupler->getEntityDependencies($userEntity);

        foreach ($userGroups as $userGroupEntity) {
            /** @var array<PolicyEntity> $groupPolicies */
            $groupPolicies = $this->userGroupToPolicyCoupler->getEntityDependencies($userGroupEntity);
            $userGroupPolicies = array_merge($userGroupPolicies, $groupPolicies);
        }

        return $userGroupPolicies;
    }

    /**
     * Check a concrete policy.
     *
     * The user has access when:
     *  - user/user's group has a policy that connected to the current application OR any application AND
     *  - user/user's group has a policy that connected to the current resource OR any resource
     *
     * @param PolicyEntity           $policyEntity
     * @param ResourceEntity|null    $resourceEntity
     * @param ApplicationEntity|null $applicationEntity
     * @return bool
     */
    private function checkPolicy(
        PolicyEntity $policyEntity,
        ?ResourceEntity $resourceEntity = null,
        ?ApplicationEntity $applicationEntity = null
    ) : bool {
        $policyResourceId = $policyEntity->getResourceId();
        $policyApplicationId = $policyEntity->getApplicationId();

        $resourceId = $resourceEntity ? $resourceEntity->getResourceId() : null;
        $applicationId = $applicationEntity ? $applicationEntity->getApplicationId() : null;

        $allowResurce = is_null($policyResourceId) || $policyResourceId === $resourceId;
        $allowApplication = is_null($policyApplicationId) || $policyApplicationId === $applicationId;

        if ($allowResurce && $allowApplication) {
            return $policyEntity->getAllowed();
        }

        return false;
    }
}