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

/**
 * Merge config arrays in the correct way.
 * This rewrites the given key->value pairs and does not make key->array(value1, value2) like the
 * `array_merge_recursive` does.
 *
 * @return array
 *
 * @throws InvalidArgumentException
 */
function merge_array_overwrite()
{
    if (func_num_args() < 2) {
        throw new \InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . ' needs two or more array arguments');
    }
    $arrays = func_get_args();
    $merged = [];

    while ($arrays) {
        $array = array_shift($arrays);
        if (!is_array($array)) {
            throw new \InvalidArgumentException(__CLASS__ . '::' . __METHOD__ . ' encountered a non array argument');
        }
        if (!$array) {
            continue;
        }
        foreach ($array as $key => $value) {
            if (is_string($key)) {
                if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                    $merged[$key] = merge_array_overwrite($merged[$key], $value);
                } else {
                    $merged[$key] = $value;
                }
            } else {
                $merged[] = $value;
            }
        }
    }
    return $merged;
}

/**
 * Gets the database config if exists.
 *
 * @return array
 */
function get_database_config()
{
    $databaseConfig = [];

    if (file_exists(__DIR__.'/local.db.php')) {
        $databaseConfig = require __DIR__.'/local.db.php';
    }

    return $databaseConfig;
}

/**
 * Gets the application config by combine the default, a custom and the read-only settings.
 *
 * @return array
 *
 * @throws Exception
 */
function get_application_config()
{
    $defaultApplicationConfig = require __DIR__.'/global.application.php';
    $localApplicationConfig = [];
    $readOnlyApplicationConfig = [
        'admin' => [
            'module'      => 'Admin',
        ],
        'website' => [
            'module'      => 'Website',
            'type'        => 'domain',
            'path'        => 'www',
        ],
    ];

    if (file_exists(__DIR__.'/local.application.php')) {
        $localApplicationConfig = require __DIR__.'/local.application.php';
    }

    return merge_array_overwrite(
        $defaultApplicationConfig,
        $localApplicationConfig,
        $readOnlyApplicationConfig
    );
}

/**
 * Reads and parses all the available theme configs.
 *
 * @return array
 */
function get_theme_config()
{
    $themeConfig = [];
    $defaultThemeConfig = file_get_contents(__DIR__.'/../resources/default_theme/config.json');

    $themeConfig['default'] = json_decode($defaultThemeConfig, true);

    $vendorThemePath = realpath(__DIR__.'/../resources/vendor_themes');
    $handle = opendir($vendorThemePath);

    if (!$handle) {
        return $themeConfig;
    }

    while (false !== ($entry = readdir($handle))) {
        if (is_dir($vendorThemePath.'/'.$entry) && file_exists($vendorThemePath.'/'.$entry.'/config.json')) {
            $vendorThemeConfig = file_get_contents($vendorThemePath.'/'.$entry.'/config.json');
            $themeConfig[$entry] = @json_decode($vendorThemeConfig, true);
        }
    }
    closedir($handle);
    return $themeConfig;
}