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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface MiddlewareInterface.
 */
interface MiddlewareInterface
{
    /**
     * A middleware is a callable. It can do whatever is appropriate with the Request and Response objects.
     * The only hard requirement is that a middleware MUST return an instance of \Psr\Http\Message\ResponseInterface.
     * Each middleware SHOULD invoke the next middleware and pass it Request and Response objects as arguments.
     *
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response);
}