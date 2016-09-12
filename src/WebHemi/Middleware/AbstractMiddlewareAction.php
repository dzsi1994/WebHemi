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
namespace WebHemi\Middleware;

use WebHemi\Adapter\Http\ResponseInterface;
use WebHemi\Adapter\Http\ServerRequestInterface;

/**
 * Class AbstractMiddlewareAction.
 */
abstract class AbstractMiddlewareAction implements MiddlewareInterface, MiddlewareActionInterface
{
    /** @var ServerRequestInterface */
    protected $request;
    /** @var ResponseInterface */
    protected $response;

    /**
     * Gets template map name or template file path.
     *
     * @return string
     */
    abstract public function getTemplateName();

    /**
     * Gets template data.
     *
     * @return array
     */
    abstract public function getTemplateData();

    /**
     * Invokes the middleware action.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    final public function __invoke(ServerRequestInterface &$request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;

        $request = $this->request
            ->withAttribute(ServerRequestInterface::REQUEST_ATTR_DISPATCH_TEMPLATE, $this->getTemplateName())
            ->withAttribute(ServerRequestInterface::REQUEST_ATTR_DISPATCH_DATA, $this->getTemplateData());

        return $this->response;
    }
}