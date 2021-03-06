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

namespace WebHemi\Configuration;

use InvalidArgumentException;

/**
 * Interface ServiceInterface.
 */
interface ServiceInterface
{
    /**
     * ServiceInterface constructor.
     *
     * @param array $config
     */
    public function __construct(array $config);

    /**
     * Checks whether the key-path does exist or not.
     *
     * @param  string $path
     * @return bool
     */
    public function has(string $path) : bool;

    /**
     * Returns the configuration data for a specific key.
     *
     * @param  string $path
     * @throws InvalidArgumentException
     * @return array
     */
    public function getData(string $path) : array;

    /**
     * Returns the configuration instance for a specific key. Also add the possibility to merge additional information
     * into it.
     *
     * @param  string $path
     * @throws InvalidArgumentException
     * @return ServiceInterface
     */
    public function getConfig(string $path) : ServiceInterface;

    /**
     * Returns the stored raw config array.
     *
     * @return array
     */
    public function toArray() : array;
}
