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
namespace WebHemiTest\MiddlewarePipeline;

use WebHemi\DateTime;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use WebHemi\Configuration\ServiceAdapter\Base\ServiceAdapter as Config;
use WebHemi\Middleware\Common\DispatcherMiddleware;
use WebHemi\Middleware\Common\RoutingMiddleware;
use WebHemi\MiddlewarePipeline\ServiceAdapter\Base\ServiceAdapter as Pipeline;
use WebHemiTest\TestExtension\AssertArraysAreSimilarTrait as AssertTrait;
use WebHemiTest\TestExtension\InvokePrivateMethodTrait;

/**
 * Class PipelineTest.
 */
class PipelineTest extends TestCase
{
    protected $config;

    use AssertTrait;
    use InvokePrivateMethodTrait;

    public function setUp()
    {
        parent::setUp();

        $config = require __DIR__ . '/../test_config.php';
        $this->config = new Config($config);
    }

    /**
     * Tests the getPipelineList() method.
     */
    public function testGetList()
    {
        $pipeline = new Pipeline($this->config);
        $pipeline->addModulePipeLine('SomeApp')
            ->start();

        $expectedPipeline = [
            'pipe2',
            RoutingMiddleware::class,
            'pipe3',
            'someModuleAlias',
            'pipe1',
            DispatcherMiddleware::class,
            'pipe4'
        ];
        $actualPipeline = $pipeline->getPipelineList();
        $this->assertArraysAreSimilar($actualPipeline, $expectedPipeline);
    }

    /**
     * Tests the getPipelineList() method.
     */
    public function testErrorOfCallNextWhenNotStarted()
    {
        $pipeline = new Pipeline($this->config);

        // Exception for not started pipeline
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1003);
        $pipeline->next();
    }

    /**
     * Tests the getPipelineList() method.
     */
    public function testErrorOfCheckMiddlewareWhenStarted()
    {
        $pipeline = new Pipeline($this->config);
        $pipeline->start();

        // Exception for already started pipeline
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1000);
        $this->invokePrivateMethod($pipeline, 'checkMiddleware', ['newService']);
    }

    /**
     * Tests the getPipelineList() method.
     */
    public function testErrorOfCheckMiddlewareWithQueuedService()
    {
        $pipeline = new Pipeline($this->config);

        // Exception for already registered item
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1001);
        $this->invokePrivateMethod($pipeline, 'checkMiddleware', ['pipe1']);
    }

    /**
     * Tests the getPipelineList() method.
     */
    public function testErrorOfCheckMiddlewareWithWrongInstance()
    {
        $pipeline = new Pipeline($this->config);

        // Exception for not-middleware class
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(1002);
        $this->invokePrivateMethod($pipeline, 'checkMiddleware', [DateTime::class]);
    }
}
