<?php
/**
 * WebHemi.
 *
 * PHP version 7.1
 *
 * @copyright 2012 - 2018 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link      http://www.gixx-web.com
 */
namespace WebHemiTest\Environment;

use InvalidArgumentException;
use WebHemi\Environment\ServiceAdapter\Base\ServiceAdapter as EnvironmentManager;
use WebHemi\Configuration\ServiceAdapter\Base\ServiceAdapter as Config;
use WebHemiTest\TestExtension\AssertArraysAreSimilarTrait as AssertTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class EnvironmentManagerTest.
 */
class EnvironmentManagerTest extends TestCase
{
    /** @var array */
    protected $config;
    /** @var array */
    protected $get = [];
    /** @var array */
    protected $post = [];
    /** @var array */
    protected $server;
    /** @var array */
    protected $cookie = [];
    /** @var array */
    protected $files = [];

    use AssertTrait;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = [
            'applications' => [
                'website' => [
                    'module' => 'Website',
                    'type'   => 'domain',
                    'path'   => '/',
                    'domain' => 'unittest.dev',
                    'language' => 'en',
                    'locale' => 'en_GB.UTF-8',
                    'timezone' => 'Europe/London',
                ],
                'admin' => [
                    'module' => 'Admin',
                    'type'   => 'directory',
                    'path'   => 'admin',
                    'domain' => 'unittest.dev',
                    'language' => 'en',
                    'locale' => 'en_GB.UTF-8',
                    'timezone' => 'Europe/London',
                ]
            ],
            'themes' => [
                'default' => [],
                'test_theme' => []
            ]
        ];
        $this->server = [
            'HTTP_HOST'    => 'unittest.dev',
            'SERVER_NAME'  => 'unittest.dev',
            'HTTP_REFERER' => 'http://foo.org?uri='.urlencode('https://www.youtube.com/'),
            'REQUEST_URI'  => '/',
            'QUERY_STRING' => '',
        ];
    }

    /**
     * Returns the config in the correct order.
     *
     * @return array
     */
    private function getOrderedConfig()
    {
        // It is important that the custom application should be checked first, then the 'admin', and the 'website' last
        $this->config['applications'] = array_reverse($this->config['applications']);

        return $this->config;
    }

    /**
     * Tests constructor with basic data.
     */
    public function testConstructor()
    {
        $config = new Config($this->config);
        $options = ['some' => 'option'];

        $testObj = new EnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files,
            $options
        );

        $this->assertInstanceOf(EnvironmentManager::class, $testObj);
        $this->assertEquals(EnvironmentManager::DEFAULT_APPLICATION, $testObj->getSelectedApplication());
        $this->assertEquals(EnvironmentManager::DEFAULT_APPLICATION_URI, $testObj->getSelectedApplicationUri());
        $this->assertEquals(EnvironmentManager::DEFAULT_MODULE, $testObj->getSelectedModule());
        $this->assertEquals(EnvironmentManager::DEFAULT_THEME, $testObj->getSelectedTheme());
        $this->assertEquals(EnvironmentManager::DEFAULT_THEME_RESOURCE_PATH, $testObj->getResourcePath());
        $this->assertEquals(realpath(__DIR__.'/../../../'), $testObj->getDocumentRoot());
        $this->assertEquals(realpath(__DIR__.'/../../..'), $testObj->getApplicationRoot());
        $this->assertArraysAreSimilar($options, $testObj->getOptions());

        $actualServerData = $testObj->getEnvironmentData('SERVER');
        $expectedServerData = $this->server;
        $expectedServerData['HTTP_REFERER'] = urldecode($expectedServerData['HTTP_REFERER']);

        $this->assertArraysAreSimilar($actualServerData, $expectedServerData);

        $this->expectException(InvalidArgumentException::class);
        $testObj->getEnvironmentData('WEBSERVER');
    }

    /**
     * Tests directory-based application.
     */
    public function testDirectoryApplicationSettings()
    {
        $this->config['applications']['TestApplication'] = [
            'domain' => 'unittest.dev',
            'type' => 'directory',
            'path' => '/test_app',
        ];
        $this->server['REQUEST_URI'] = '/test_app/some_page';

        $config = new Config($this->getOrderedConfig());

        $testObj = new EnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files,
            []
        );

        $this->assertInstanceOf(EnvironmentManager::class, $testObj);
        $this->assertEquals('TestApplication', $testObj->getSelectedApplication());
        $this->assertEquals('/test_app', $testObj->getSelectedApplicationUri());
        $this->assertEquals('/test_app/some_page', $testObj->getRequestUri());
    }

    /**
     * Tests domain-based application.
     */
    public function testDomainApplicationSettings()
    {
        $this->config['applications']['TestApplication'] = [
            'domain' => 'test.app.unittest.dev',
            'type' => 'domain',
            'path' => '/',
        ];
        $this->server['HTTP_HOST'] = 'test.app.unittest.dev';
        $this->server['SERVER_NAME'] = 'test.app.unittest.dev';
        $this->server['REQUEST_URI'] = '/test_app/some_page';

        $config = new Config($this->getOrderedConfig());

        $testObj = new EnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files,
            []
        );

        $this->assertInstanceOf(EnvironmentManager::class, $testObj);
        $this->assertEquals('TestApplication', $testObj->getSelectedApplication());
        $this->assertEquals('/', $testObj->getSelectedApplicationUri());
        $this->assertEquals('test.app.unittest.dev', $testObj->getApplicationDomain());
        $this->assertFalse($testObj->isSecuredApplication());
    }

    /**
     * Tests getting client IP.
     */
    public function testGetClientIp()
    {
        $config = new Config($this->getOrderedConfig());
        $testObj = new EnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files,
            []
        );
        $this->assertEmpty($testObj->getClientIp());

        $this->server['REMOTE_ADDR'] = 'some_ip';
        $testObj = new EnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files,
            []
        );
        $this->assertSame('some_ip', $testObj->getClientIp());

        $this->server['HTTP_X_FORWARDED_FOR'] = 'some_forwarded_ip';
        $testObj = new EnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files,
            []
        );
        $this->assertSame('some_forwarded_ip', $testObj->getClientIp());

        $this->server['REMOTE_ADDR'] = 'some_other_ip';
        $testObj = new EnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files,
            []
        );
        $this->assertSame('some_forwarded_ip', $testObj->getClientIp());
    }

    /**
     * Tests vendor theme resource path.
     */
    public function testThemePathSettings()
    {
        $this->config['applications']['TestApplication'] = [
            'domain' => 'test.app.unittest.dev',
            'type' => 'domain',
            'path' => '/',
            'theme' => 'test_theme'

        ];
        $this->server['HTTP_HOST'] = 'test.app.unittest.dev';
        $this->server['SERVER_NAME'] = 'test.app.unittest.dev';
        $this->server['REQUEST_URI'] = '/test_app/some_page';

        $config = new Config($this->getOrderedConfig());

        $testObj = new EnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files,
            []
        );

        $this->assertInstanceOf(EnvironmentManager::class, $testObj);
        $this->assertEquals('TestApplication', $testObj->getSelectedApplication());
        $this->assertEquals('/', $testObj->getSelectedApplicationUri());
        $this->assertEquals('/resources/vendor_themes/test_theme', $testObj->getResourcePath());
    }

    /**
     * Tests setDomain() error.
     */
    public function testSetDomainWithIp()
    {
        $this->config['applications']['TestApplication'] = [
            'domain' => '192.168.100.12',
            'type' => 'domain',
            'path' => '/',
            'theme' => 'test_theme'

        ];
        $this->server['HTTP_HOST'] = '192.168.100.12';
        $this->server['SERVER_NAME'] = '192.168.100.12';
        $this->server['REQUEST_URI'] = '/test_app/some_page';

        $config = new Config($this->getOrderedConfig());
        $expectedError = 'This application does not support IP access';

        try {
            new EnvironmentManager(
                $config,
                $this->get,
                $this->post,
                $this->server,
                $this->cookie,
                $this->files,
                []
            );
        } catch (\Throwable $exception) {
            $this->assertSame($expectedError, $exception->getMessage());
        }
    }
}
