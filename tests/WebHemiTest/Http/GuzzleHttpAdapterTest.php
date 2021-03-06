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
namespace WebHemiTest\Http;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use WebHemi\Http\ServiceInterface as HttpAdapterInterface;
use WebHemi\Http\ServiceAdapter\GuzzleHttp\ServiceAdapter as GuzzleHttpAdapter;
use WebHemi\Configuration\ServiceAdapter\Base\ServiceAdapter as Config;
use WebHemiTest\TestExtension\InvokePrivateMethodTrait;
use WebHemiTest\TestService\EmptyEnvironmentManager;
use PHPUnit\Framework\TestCase;

/**
 * Class GuzzleHttpAdapterTest.
 */
class GuzzleHttpAdapterTest extends TestCase
{
    /** @var array */
    protected $config = [];
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

    use InvokePrivateMethodTrait;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = require __DIR__ . '/../test_config.php';
        $this->server = [
            'HTTP_HOST'    => 'unittest.dev',
            'SERVER_NAME'  => 'unittest.dev',
            'REQUEST_URI'  => '/',
            'QUERY_STRING' => '',
        ];
    }

    /**
     * Tests constructor.
     */
    public function testConstructor()
    {
        $config = new Config($this->config);
        $environmentManager = new EmptyEnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files
        );

        $testObj = new GuzzleHttpAdapter($environmentManager);

        $this->assertInstanceOf(HttpAdapterInterface::class, $testObj);
        $this->assertInstanceOf(ServerRequest::class, $testObj->getRequest());
        $this->assertInstanceOf(Response::class, $testObj->getResponse());
    }

    /**
     * Tests if can determine the right scheme.
     */
    public function testGetScheme()
    {
        $config = new Config($this->config);
        $environmentManager = new EmptyEnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files
        );

        $testObj = new GuzzleHttpAdapter($environmentManager);

        $result = $this->invokePrivateMethod($testObj, 'getScheme');
        $this->assertEquals('http', $result);

        $server = $this->server;
        $server['HTTPS'] = 'on';

        $environmentManager = new EmptyEnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $server,
            $this->cookie,
            $this->files
        );

        $testObj = new GuzzleHttpAdapter($environmentManager);

        $result = $this->invokePrivateMethod($testObj, 'getScheme');
        $this->assertEquals('https', $result);
    }

    /**
     * Tests if can determine the right host.
     */
    public function testGetHost()
    {
        $server = $this->server;
        $server['HTTP_HOST'] = '';
        $server['SERVER_NAME']  = 'unittest.dev:8080';

        $config = new Config($this->config);
        $environmentManager = new EmptyEnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $server,
            $this->cookie,
            $this->files
        );

        $testObj = new GuzzleHttpAdapter($environmentManager);

        $result = $this->invokePrivateMethod($testObj, 'getHost');
        $this->assertEquals('unittest.dev', $result);
    }

    /**
     * Tests if can determine the right protocol.
     */
    public function testGetProtocol()
    {
        $config = new Config($this->config);
        $environmentManager = new EmptyEnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $this->server,
            $this->cookie,
            $this->files
        );

        $testObj = new GuzzleHttpAdapter($environmentManager);

        $result = $this->invokePrivateMethod($testObj, 'getProtocol');
        $this->assertEquals('1.1', $result);

        $server = $this->server;
        $server['SERVER_PROTOCOL'] = 'HTTP/1.0';

        $environmentManager = new EmptyEnvironmentManager(
            $config,
            $this->get,
            $this->post,
            $server,
            $this->cookie,
            $this->files
        );

        $testObj = new GuzzleHttpAdapter($environmentManager);

        $result = $this->invokePrivateMethod($testObj, 'getProtocol');
        $this->assertEquals('1.0', $result);
    }
}
