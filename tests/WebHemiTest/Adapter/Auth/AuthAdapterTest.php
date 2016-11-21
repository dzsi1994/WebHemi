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

namespace WebHemiTest\Adapter\Auth;

use Prophecy\Argument;
use WebHemi\Adapter\Auth\AuthAdapterInterface;
use WebHemi\Adapter\Data\DataAdapterInterface;
use WebHemi\Auth\Result;
use WebHemi\Auth\AuthStorageInterface;
use WebHemi\Config\Config;
use WebHemi\Data\Entity\User\UserEntity;
use WebHemi\Data\Storage\DataStorageInterface;
use WebHemiTest\Fixtures\EmptyAuthAdapter;
use WebHemiTest\Fixtures\EmptyAuthStorage;
use WebHemiTest\Fixtures\EmptyEntity;
use WebHemiTest\Fixtures\EmptyStorage;
use WebHemiTest\AssertTrait;
use WebHemiTest\InvokePrivateMethodTrait;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class AuthAdapterTest
 */
class AuthAdapterTest extends TestCase
{
    /** @var array */
    private $config;

    use AssertTrait;
    use InvokePrivateMethodTrait;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();

        $this->config = require __DIR__ . '/../../Fixtures/test_config.php';
    }

    /**
     * Tests class constructor.
     */
    public function testConstructor()
    {
        $defaultAdapter = $this->prophesize(DataAdapterInterface::class);
        $defaultAdapter->setDataGroup(Argument::type('string'))->willReturn(1);
        $defaultAdapter->setIdKey(Argument::type('string'))->willReturn(1);
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $defaultAdapter->reveal();

        $config = new Config($this->config);
        $result = new Result();
        $authStorage = new EmptyAuthStorage();
        $dataEntity = new EmptyEntity();
        $dataStorage = new EmptyStorage($defaultAdapterInstance, $dataEntity);

        $adapter = new EmptyAuthAdapter(
            $config,
            $result,
            $authStorage,
            $dataStorage
        );

        $this->assertInstanceOf(AuthAdapterInterface::class, $adapter);
        $actualObject = $this->invokePrivateMethod($adapter, 'getAuthStorage', []);
        $this->assertInstanceOf(AuthStorageInterface::class, $actualObject);
        $actualObject = $this->invokePrivateMethod($adapter, 'getDataStorage', []);
        $this->assertInstanceOf(DataStorageInterface::class, $actualObject);
        $actualObject = $this->invokePrivateMethod($adapter, 'getAuthResult', []);
        $this->assertInstanceOf(Result::class, $actualObject);
        $this->assertFalse($result === $actualObject);
    }

    /**
     * Tests authentication.
     */
    public function testAuthenticate()
    {
        $defaultAdapter = $this->prophesize(DataAdapterInterface::class);
        $defaultAdapter->setDataGroup(Argument::type('string'))->willReturn(1);
        $defaultAdapter->setIdKey(Argument::type('string'))->willReturn(1);
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $defaultAdapter->reveal();

        $config = new Config($this->config);
        $result = new Result();
        $authStorage = new EmptyAuthStorage();
        $dataEntity = new UserEntity();
        $dataStorage = new EmptyStorage($defaultAdapterInstance, $dataEntity);

        $adapter = new EmptyAuthAdapter(
            $config,
            $result,
            $authStorage,
            $dataStorage
        );

        $this->assertFalse($adapter->hasIdentity());
        $this->assertNull($adapter->getIdentity());

        $adapter->authResultShouldBe = Result::FAILURE_OTHER;
        $result = $adapter->authenticate();
        $this->assertSame(Result::FAILURE_OTHER, $result->getCode());
        $this->assertNull($result->getIdentity());
        $this->assertNotEmpty($result->getMessage());

        $adapter->authResultShouldBe = Result::FAILURE_CREDENTIAL_INVALID;
        $result = $adapter->authenticate();
        $this->assertSame(Result::FAILURE_CREDENTIAL_INVALID, $result->getCode());
        $this->assertNull($result->getIdentity());
        $this->assertNotEmpty($result->getMessage());

        $adapter->authResultShouldBe = Result::FAILURE_IDENTITY_NOT_FOUND;
        $result = $adapter->authenticate();
        $this->assertSame(Result::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
        $this->assertNull($result->getIdentity());
        $this->assertNotEmpty($result->getMessage());

        $adapter->authResultShouldBe = Result::FAILURE;
        $result = $adapter->authenticate();
        $this->assertSame(Result::FAILURE, $result->getCode());
        $this->assertNull($result->getIdentity());
        $this->assertNotEmpty($result->getMessage());

        $adapter->authResultShouldBe = Result::SUCCESS;
        $result = $adapter->authenticate();
        $this->assertSame(Result::SUCCESS, $result->getCode());
        $this->assertInstanceOf(UserEntity::class, $result->getIdentity());
        $this->assertSame('test', $result->getIdentity()->getUserName());

        $adapter->clearIdentity();
        $this->assertNull($adapter->getIdentity());
    }

    /**
     * Tests setIdentity() method.
     */
    public function testSetIdentity()
    {
        $defaultAdapter = $this->prophesize(DataAdapterInterface::class);
        $defaultAdapter->setDataGroup(Argument::type('string'))->willReturn(1);
        $defaultAdapter->setIdKey(Argument::type('string'))->willReturn(1);
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $defaultAdapter->reveal();

        $config = new Config($this->config);
        $result = new Result();
        $authStorage = new EmptyAuthStorage();
        $dataEntity = new UserEntity();
        $dataEntity->setUserName('new entity');
        $dataStorage = new EmptyStorage($defaultAdapterInstance, $dataEntity);

        $adapter = new EmptyAuthAdapter(
            $config,
            $result,
            $authStorage,
            $dataStorage
        );

        $this->assertFalse($adapter->hasIdentity());
        $this->assertNull($adapter->getIdentity());

        $adapter->setIdentity($dataEntity);
        $this->assertInstanceOf(UserEntity::class, $adapter->getIdentity());
        $this->assertTrue($dataEntity === $adapter->getIdentity());
        $this->assertSame('new entity', $adapter->getIdentity()->getUserName());
    }

    /**
     * Tests auth adapter Result
     *
     * @covers \WebHemi\Auth\Result
     */
    public function testResult()
    {
        $defaultAdapter = $this->prophesize(DataAdapterInterface::class);
        $defaultAdapter->setDataGroup(Argument::type('string'))->willReturn(1);
        $defaultAdapter->setIdKey(Argument::type('string'))->willReturn(1);
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $defaultAdapter->reveal();

        $config = new Config($this->config);
        $result = new Result();
        $authStorage = new EmptyAuthStorage();
        $dataEntity = new UserEntity();
        $dataStorage = new EmptyStorage($defaultAdapterInstance, $dataEntity);

        $adapter = new EmptyAuthAdapter(
            $config,
            $result,
            $authStorage,
            $dataStorage
        );

        $adapter->authResultShouldBe = Result::SUCCESS;
        $result = $adapter->authenticate();
        $this->assertTrue($result->isValid());
        $this->assertSame(Result::SUCCESS, $result->getCode());

        $adapter->authResultShouldBe = Result::FAILURE;
        $result = $adapter->authenticate();
        $this->assertFalse($result->isValid());
        $this->assertSame(Result::FAILURE, $result->getCode());

        // set it to a non-valid result code
        $adapter->authResultShouldBe = -100;
        $result = $adapter->authenticate();
        $this->assertFalse($result->isValid());
        $this->assertSame(Result::FAILURE_OTHER, $result->getCode());
    }
}