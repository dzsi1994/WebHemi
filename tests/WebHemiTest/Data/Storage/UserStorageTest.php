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
namespace WebHemiTest\Data\Storage;

use WebHemi\DateTime;
use Prophecy\Argument;
use WebHemi\Data\ConnectorInterface as DataAdapterInterface;
use WebHemi\Data\Storage\User\UserStorage;
use WebHemi\Data\Entity\User\UserEntity;
use WebHemiTest\TestExtension\AssertArraysAreSimilarTrait as AssertTrait;
use WebHemiTest\TestExtension\InvokePrivateMethodTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class UserStorageTest.
 */
class UserStorageTest extends TestCase
{
    private $defaultAdapter;

    use AssertTrait;
    use InvokePrivateMethodTrait;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $defaultAdapter = $this->prophesize(DataAdapterInterface::class);
        $defaultAdapter->setDataGroup(Argument::type('string'))->willReturn($defaultAdapter->reveal());
        $defaultAdapter->setIdKey(Argument::type('string'))->willReturn($defaultAdapter->reveal());

        $this->defaultAdapter = $defaultAdapter;
    }

    /**
     * Test constructor.
     *
     * @covers \WebHemi\Data\Storage\AbstractStorage
     */
    public function testStorageInit()
    {
        $dataEntity = new UserEntity();
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $this->defaultAdapter->reveal();
        $storage = new UserStorage($defaultAdapterInstance, $dataEntity);

        $this->assertInstanceOf(UserStorage::class, $storage);
        $this->assertTrue($storage->initialized());

        $this->assertAttributeEquals('webhemi_user', 'dataGroup', $storage);
        $this->assertAttributeEquals('id_user', 'idKey', $storage);

        // objects are not the same --> cloned.
        $this->assertInstanceOf(DataAdapterInterface::class, $storage->getConnector());
        $this->assertFalse($defaultAdapterInstance === $storage->getConnector());

        // objects are not the same --> cloned.
        $this->assertInstanceOf(UserEntity::class, $storage->createEntity());
        $this->assertFalse($dataEntity === $storage->createEntity());
    }

    /**
     * Test the getUsers method.
     */
    public function testGetUsers()
    {
        $data = [
            0 => [
                'id_user' => 1,
                'username' => 'testUser1',
                'email' => 'test1.address@foo.org',
                'password' => md5('testPassword'),
                'hash' => 'a',
                'is_active' => 1,
                'is_enabled' => 1,
                'date_created' =>  '2016-03-10 16:25:12',
                'date_modified' => '2017-04-20 16:25:12',
            ],
            1 => [
                'id_user' => 2,
                'username' => 'testUser2',
                'email' => 'test2.address@foo.org',
                'password' => md5('testPassword'),
                'hash' => 'b',
                'is_active' => 0,
                'is_enabled' => 0,
                'date_created' =>  '2016-03-10 16:25:12',
                'date_modified' => '2017-04-20 16:25:12',
            ],
            2 => [
                'id_user' => 3,
                'username' => 'testUser3',
                'email' => 'test3.address@foo.org',
                'password' => md5('testPassword'),
                'hash' => 'c',
                'is_active' => 1,
                'is_enabled' => 1,
                'date_created' =>  '2016-03-10 16:25:12',
                'date_modified' => '2017-04-20 16:25:12',
            ],
        ];

        $this->defaultAdapter
            ->getDataSet(Argument::type('array'), Argument::type('array'))
            ->will(
                function ($args) use ($data) {
                    if (empty($args[0])) {
                        return $data;
                    }
                    return [];
                }
            );

        $dataEntity = new UserEntity();
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $this->defaultAdapter->reveal();
        $storage = new userStorage($defaultAdapterInstance, $dataEntity);

        /** @var UserEntity[] $actualResult */
        $actualResult = $storage->getUserList();
        $this->assertSame(3, count($actualResult));

        $this->assertInstanceOf(UserEntity::class, $actualResult[0]);
        $this->assertSame('test1.address@foo.org', $actualResult[0]->getEmail());
        $this->assertTrue($actualResult[0]->getActive());
        $actualData = $this->invokePrivateMethod($storage, 'getEntityData', [$actualResult[0]]);

        $this->assertArraysAreSimilar($data[0], $actualData);

        $this->assertInstanceOf(UserEntity::class, $actualResult[1]);
        $this->assertFalse($actualResult[1]->getActive());
        $this->assertSame('test2.address@foo.org', $actualResult[1]->getEmail());
        $actualData = $this->invokePrivateMethod($storage, 'getEntityData', [$actualResult[1]]);
        $this->assertArraysAreSimilar($data[1], $actualData);

        $this->assertInstanceOf(UserEntity::class, $actualResult[1]);
        $this->assertTrue($actualResult[2]->getActive());
        $this->assertSame('test3.address@foo.org', $actualResult[2]->getEmail());
        $actualData = $this->invokePrivateMethod($storage, 'getEntityData', [$actualResult[2]]);
        $this->assertArraysAreSimilar($data[2], $actualData);
    }

    /**
     * Test the getUserById() method.
     */
    public function testGetUserById()
    {
        $data = [
            0 => [
                'id_user' => 1,
                'username' => 'testUser',
                'email' => 'test.address@foo.org',
                'password' => md5('testPassword'),
                'hash' => '',
                'is_active' => true,
                'is_enabled' => true,
                'date_created' =>  '2016-03-24 16:25:12',
                'date_modified' =>  null,
            ]
        ];

        $this->defaultAdapter
            ->getDataSet(Argument::type('array'), Argument::type('array'))
            ->will(
                function ($args) use ($data) {
                    if ($args[0]['id_user'] == 1) {
                        return $data;
                    }

                    return [];
                }
            );

        $dataEntity = new UserEntity();
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $this->defaultAdapter->reveal();
        $storage = new UserStorage($defaultAdapterInstance, $dataEntity);

        $actualResult = $storage->getUserById(3);
        $this->assertEmpty($actualResult);

        /** @var UserEntity $actualResult */
        $actualResult = $storage->getUserById(1);
        $this->assertInstanceOf(UserEntity::class, $actualResult);
        $this->assertFalse($dataEntity === $actualResult);
        $this->assertInstanceOf(DateTime::class, $actualResult->getDateCreated());
        $this->assertEquals($data[0]['password'], $actualResult->getPassword());
        $this->assertSame(true, $actualResult->getEnabled());
    }

    /**
     * Test the getUserByEmail() method.
     */
    public function testGetUserByEmail()
    {
        $data = [
            0 => [
                'id_user' => 1,
                'username' => 'testUser',
                'email' => 'test.address@foo.org',
                'password' => md5('testPassword'),
                'hash' => '',
                'is_active' => 1,
                'is_enabled' => 1,
                'date_created' =>  '2016-03-24 16:25:12',
                'date_modified' =>  '2016-03-24 16:25:12',
            ]
        ];

        $this->defaultAdapter
            ->getDataSet(Argument::type('array'), Argument::type('array'))
            ->will(
                function ($args) use ($data) {
                    if ($args[0]['email'] == 'test.address@foo.org') {
                        return $data;
                    }
                    return [];
                }
            );

        $dataEntity = new UserEntity();
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $this->defaultAdapter->reveal();
        $storage = new UserStorage($defaultAdapterInstance, $dataEntity);

        $actualResult = $storage->getUserByEmail('wrong.address@foo.org');
        $this->assertEmpty($actualResult);

        /** @var UserEntity $actualResult */
        $actualResult = $storage->getUserByEmail('test.address@foo.org');
        $this->assertInstanceOf(UserEntity::class, $actualResult);
        $this->assertFalse($dataEntity === $actualResult);
        $this->assertInstanceOf(DateTime::class, $actualResult->getDateCreated());
        $this->assertEquals($data[0]['password'], $actualResult->getPassword());
        $this->assertSame(true, $actualResult->getEnabled());

        $actualData = $this->invokePrivateMethod($storage, 'getEntityData', [$actualResult]);
        $this->assertArraysAreSimilar($data[0], $actualData);
    }

    /**
     * Test the getUserByUserName() method.
     */
    public function testGetUserByUserName()
    {
        $data = [
            'id_user' => 1,
            'username' => 'testUser',
            'email' => 'test.address@foo.org',
            'password' => md5('testPassword'),
            'hash' => '',
            'is_active' => 1,
            'is_enabled' => 1,
            'date_created' =>  '2016-03-24 16:25:12',
            'date_modified' =>  '2016-03-24 16:25:12',
        ];

        $this->defaultAdapter
            ->getDataSet(Argument::type('array'), Argument::type('array'))
            ->will(
                function ($args) use ($data) {
                    if ($args[0]['username'] == 'testUser') {
                        return [$data];
                    }
                    return [];
                }
            );

        $dataEntity = new UserEntity();
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $this->defaultAdapter->reveal();
        $storage = new UserStorage($defaultAdapterInstance, $dataEntity);

        $actualResult = $storage->getUserByUserName('Donald Trump');
        $this->assertEmpty($actualResult);

        /** @var UserEntity $actualResult */
        $actualResult = $storage->getUserByUserName('testUser');
        $this->assertInstanceOf(UserEntity::class, $actualResult);
        $this->assertFalse($dataEntity === $actualResult);
        $this->assertInstanceOf(DateTime::class, $actualResult->getDateCreated());
        $this->assertEquals($data['password'], $actualResult->getPassword());
        $this->assertSame(true, $actualResult->getEnabled());

        $actualData = $this->invokePrivateMethod($storage, 'getEntityData', [$actualResult]);
        $this->assertArraysAreSimilar($data, $actualData);
    }

    /**
     * Test the getUserByCredentials() method.
     */
    public function testGetUserByCredentials()
    {
        $data = [
            'id_user' => 1,
            'username' => 'testUser',
            'email' => 'test.address@foo.org',
            'password' => md5('testPassword'),
            'hash' => '',
            'is_active' => 1,
            'is_enabled' => 1,
            'date_created' =>  '2016-03-24 16:25:12',
            'date_modified' =>  '2016-03-24 16:25:12',
        ];

        $this->defaultAdapter
            ->getDataSet(Argument::type('array'), Argument::type('array'))
            ->will(
                function ($args) use ($data) {
                    if ($args[0]['username'] == 'testUser'
                        && $args[0]['password'] == md5('testPassword')
                    ) {
                        return [$data];
                    }
                    return [];
                }
            );

        $dataEntity = new UserEntity();
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $this->defaultAdapter->reveal();
        $storage = new UserStorage($defaultAdapterInstance, $dataEntity);

        $actualResult = $storage->getUserByCredentials('Donald Trump', 'testPassword');
        $this->assertEmpty($actualResult);

        $actualResult = $storage->getUserByCredentials('testUser', 'testPassword');
        $this->assertEmpty($actualResult);

        /** @var UserEntity $actualResult */
        $actualResult = $storage->getUserByCredentials('testUser', md5('testPassword'));
        $this->assertInstanceOf(UserEntity::class, $actualResult);
        $this->assertFalse($dataEntity === $actualResult);
        $this->assertInstanceOf(DateTime::class, $actualResult->getDateCreated());
        $this->assertEquals($data['password'], $actualResult->getPassword());
        $this->assertSame(true, $actualResult->getEnabled());

        $actualData = $this->invokePrivateMethod($storage, 'getEntityData', [$actualResult]);
        $this->assertArraysAreSimilar($data, $actualData);
    }
}
