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

namespace WebHemi\Middleware\Action\Admin\ControlPanel\Themes;

use WebHemi\Middleware\Action\AbstractMiddlewareAction;

/**
 * Class DeleteAction
 */
class DeleteAction extends AbstractMiddlewareAction
{
    /**
     * Gets template map name or template file path.
     *
     * @return string
     */
    public function getTemplateName() : string
    {
        return 'admin-control-panel-themes-delete';
    }

    /**
     * Gets template data.
     *
     * @return array
     */
    public function getTemplateData() : array
    {
        return [];
    }
}
