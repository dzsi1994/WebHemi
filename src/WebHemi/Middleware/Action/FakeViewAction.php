<?php

namespace WebHemi\Middleware\Action;

use WebHemi\Adapter\Http\ServerRequestInterface;
use WebHemi\Data\Entity\User\UserEntity;
use WebHemi\Data\Storage\User\UserStorage;
use WebHemi\Middleware\AbstractMiddlewareAction;

/**
 * Class FakeViewAction
 *
 * @codeCoverageIgnore - only for test purposes
 */
class FakeViewAction extends AbstractMiddlewareAction
{
    /** @var UserStorage */
    private $userStorage;

    private $template = 'blog-post';

    public function __construct(UserStorage $userStorage)
    {
        $this->userStorage = $userStorage;
    }

    public function getTemplateName() : string
    {
        return $this->template;
    }

    public function getTemplateData() : array
    {
        /** @var UserEntity $userEntity */
        $userEntity = $this->userStorage->getUserById(1);
        $routingParams = $this->request->getAttribute(ServerRequestInterface::REQUEST_ATTR_ROUTING_PARAMETERS);

        return [
            'blogPost' => [
                'title'       => 'Fake test',
                'publishedAt' => time(),
                'author'      => [
                    'name' => $userEntity->getUserName()
                ],
                'content'     => 'Lorem ipsum dolor sit amet...',
                'parameter'   => $routingParams
            ]
        ];
    }
}
