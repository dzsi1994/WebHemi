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
namespace WebHemi\Form\Element\Web;

/**
 * Class HiddenElement.
 */
class HiddenElement extends AbstractElement
{
    /**
     * Returns the element type.
     *
     * @return string
     */
    public function getType()
    {
        return 'hidden';
    }
}
