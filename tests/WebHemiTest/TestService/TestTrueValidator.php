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
namespace WebHemiTest\TestService;

use WebHemi\Validator\ServiceInterface as ValidatorInterface;

/**
 * Class TestTrueValidator.
 */
class TestTrueValidator implements ValidatorInterface
{
    /**
     * Validates data.
     *
     * @param mixed $data
     * @return boolean
     */
    public function validate($data) : bool
    {
        unset($data);
        return true;
    }

    /**
     * Gets error from validation.
     *
     * @return array
     */
    public function getErrors() : array
    {
        return [];
    }
}