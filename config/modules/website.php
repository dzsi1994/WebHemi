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

return [
    'Website' => [
        'routing' => [
            'index' => [
                'path'            => '/',
                'middleware'      => \WebHemi\Middleware\Action\FakeAction::class,
                'allowed_methods' => ['GET'],
            ]
        ],
    ],
];