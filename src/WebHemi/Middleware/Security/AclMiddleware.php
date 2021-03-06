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

namespace WebHemi\Middleware\Security;

use RuntimeException;
use WebHemi\Acl\ServiceInterface as AclInterface;
use WebHemi\Auth\ServiceInterface as AuthInterface;
use WebHemi\Http\ResponseInterface;
use WebHemi\Http\ServerRequestInterface;
use WebHemi\Environment\ServiceInterface as EnvironmentInterface;
use WebHemi\Data\Entity;
use WebHemi\Data\Storage;
use WebHemi\Middleware\Action;
use WebHemi\Middleware\MiddlewareInterface;

/**
 * Class AclMiddleware.
 */
class AclMiddleware implements MiddlewareInterface
{
    /**
     * @var AuthInterface
     */
    private $authAdapter;
    /**
     * @var AclInterface
     */
    private $aclAdapter;
    /**
     * @var EnvironmentInterface
     */
    private $environmentManager;
    /**
     * @var Storage\ApplicationStorage
     */
    private $applicationStorage;
    /**
     * @var Storage\ResourceStorage
     */
    private $resourceStorage;
    /**
     * @var Storage\UserStorage
     */
    private $userStorage;
    /**
     * @var array
     */
    private $middlewareWhiteList = [
        Action\Auth\LoginAction::class,
        Action\Auth\LogoutAction::class,
    ];

    /**
     * AclMiddleware constructor.
     *
     * @param AuthInterface               $authAdapter
     * @param AclInterface                $aclAdapter
     * @param EnvironmentInterface        $environmentManager
     * @param Storage\ApplicationStorage  $applicationStorage
     * @param Storage\ResourceStorage     $resourceStorage
     * @param Storage\UserStorage         $userStorage
     */
    public function __construct(
        AuthInterface $authAdapter,
        AclInterface $aclAdapter,
        EnvironmentInterface $environmentManager,
        Storage\ApplicationStorage $applicationStorage,
        Storage\ResourceStorage $resourceStorage,
        Storage\UserStorage $userStorage
    ) {
        $this->authAdapter = $authAdapter;
        $this->aclAdapter = $aclAdapter;
        $this->environmentManager = $environmentManager;
        $this->applicationStorage = $applicationStorage;
        $this->resourceStorage = $resourceStorage;
        $this->userStorage = $userStorage;
    }

    /**
     * A middleware is a callable. It can do whatever is appropriate with the Request and Response objects.
     * The only hard requirement is that a middleware MUST return an instance of \Psr\Http\Message\ResponseInterface.
     * Each middleware SHOULD invoke the next middleware and pass it Request and Response objects as arguments.
     *
     * @param  ServerRequestInterface $request
     * @param  ResponseInterface      $response
     * @throws RuntimeException
     * @return void
     */
    public function __invoke(ServerRequestInterface&$request, ResponseInterface&$response) : void
    {
        $actionMiddleware = $request->getAttribute(ServerRequestInterface::REQUEST_ATTR_RESOLVED_ACTION_CLASS);
        $resourceName = $request->getAttribute(ServerRequestInterface::REQUEST_ATTR_ROUTING_RESOURCE);

        if (in_array($actionMiddleware, $this->middlewareWhiteList)) {
            return;
        }

        /**
         * @var Entity\UserEntity|null $identity
         */
        $identity = $this->authAdapter->getIdentity();

        if ($identity instanceof Entity\UserEntity) {
            /**
             * @var Entity\ApplicationEntity $applicationEntity
             */
            $applicationEntity = $this->applicationStorage
                ->getApplicationByName($this->environmentManager->getSelectedApplication());
            /**
             * @var Entity\ResourceEntity $resourceEntity
             */
            $resourceEntity = $this->resourceStorage->getResourceByName($resourceName);
            // Check the user against the application and resource
            $hasAccess = $this->aclAdapter->isAllowed($identity, $resourceEntity, $applicationEntity);

            $request = $this->setIdentityForTemplate($request, $identity);

            if (!$hasAccess) {
                throw new RuntimeException('Forbidden', 403);
            }
        } else {
            // Instead of throw a useless 401 error here, redirect the user to the login page
            $appUri = rtrim($this->environmentManager->getSelectedApplicationUri(), '/');

            $response = $response->withStatus(ResponseInterface::STATUS_REDIRECT, 'Found')
                ->withHeader('Location', $appUri.'/auth/login');
        }
    }

    /**
     * Set identified user data for the templates
     *
     * @param  ServerRequestInterface $request
     * @param  Entity\UserEntity $identity
     * @return ServerRequestInterface
     */
    private function setIdentityForTemplate(
        ServerRequestInterface $request,
        Entity\UserEntity $identity
    ) : ServerRequestInterface {
        // Set authenticated user for the templates
        $templateData = $request->getAttribute(ServerRequestInterface::REQUEST_ATTR_DISPATCH_DATA, []);
        $templateData[ServerRequestInterface::REQUEST_ATTR_AUTHENTICATED_USER] = $identity;
        $templateData[ServerRequestInterface::REQUEST_ATTR_AUTHENTICATED_USER_META] = $this->userStorage
            ->getUserMetaListByUser((int) $identity->getUserId());

        return $request->withAttribute(ServerRequestInterface::REQUEST_ATTR_DISPATCH_DATA, $templateData);
    }
}
