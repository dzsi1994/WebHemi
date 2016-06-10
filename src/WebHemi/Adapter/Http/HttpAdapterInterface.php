<?php
/**
 * WebHemi
 *
 * PHP version 5.6
 *
 * @copyright 2012 - 2016 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 * @link      http://www.gixx-web.com
 */

namespace WebHemi\Adapter\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Interface AdapterInterface
 *
 * @package WebHemi\Adapter\Http
 */
interface AdapterInterface
{
    /**
     * AdapterInterface constructor.
     *
     * @param array $server
     * @param array $get
     * @param array $post
     * @param array $cookie
     * @param array $files
     */
    public function __construct(array $server, array $get, array $post, array $cookie, array $files);

    /**
     * Returns the HTTP request.
     *
     * @return ServerRequestInterface
     */
    public function getRequest();

    /**
     * Returns the response being sent.
     *
     * @return ResponseInterface
     */
    public function getResponse();

    /**
     * Override the response being sent out.
     *
     * @param ResponseInterface $response
     */
    public function overrideResponse(ResponseInterface $response);

    /**
     * Create a stream for a response from a string.
     *
     * @param string $string
     *
     * @return StreamInterface
     */
    public function getStringStream($string);
}
