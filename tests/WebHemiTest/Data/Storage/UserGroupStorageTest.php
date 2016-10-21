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
namespace WebHemiTest\Data\Storage;

use Prophecy\Argument;
use WebHemi\Adapter\Data\DataAdapterInterface;
use WebHemi\Data\Storage\User\UserGroupStorage;
use WebHemi\Data\Entity\User\UserGroupEntity;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class UserGroupStorageTest
 */
class UserGroupStorageTest extends TestCase
{
    private $defaultAdapter;

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $defaultAdapter = $this->prophesize(DataAdapterInterface::class);
        $defaultAdapter->setDataGroup(Argument::type('string'))->willReturn(1);
        $defaultAdapter->setIdKey(Argument::type('string'))->willReturn(1);

        $this->defaultAdapter = $defaultAdapter;
    }

    /**
     * Test constructor.
     *
     * @covers \WebHemi\Data\Storage\AbstractDataStorage
     */
    public function testStorageInit()
    {
        $dataEntity = new UserGroupEntity();
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $this->defaultAdapter->reveal();
        $storage = new UserGroupStorage($defaultAdapterInstance, $dataEntity);

        $this->assertInstanceOf(UserGroupStorage::class, $storage);
        $this->assertTrue($storage->initialized());

        $this->assertAttributeEquals('webhemi_user_group', 'dataGroup', $storage);
        $this->assertAttributeEquals('id_user_group', 'idKey', $storage);

        // objects are not the same --> cloned.
        $this->assertInstanceOf(DataAdapterInterface::class, $storage->getDataAdapter());
        $this->assertFalse($defaultAdapterInstance === $storage->getDataAdapter());

        // objects are not the same --> cloned.
        $this->assertInstanceOf(UserGroupEntity::class, $storage->createEntity());
        $this->assertFalse($dataEntity === $storage->createEntity());
    }

    /**
     * Test the getUserGroupById method.
     */
    public function testGetUserGroupById()
    {
        $data = [
            [
                'id_user_group' => 1,
                'title' => 'Admins',
                'description' => 'Administrator group',
                'is_read_only' => true,
                'date_created' =>  '2016-03-24 16:25:12',
                'date_modified' =>  null,
            ],
            [
                'id_user_group' => 2,
                'title' => 'Guests',
                'description' => 'Visitor group',
                'is_read_only' => false,
                'date_created' =>  '2016-03-24 16:25:12',
                'date_modified' =>  null,
            ],
        ];

        $this->defaultAdapter
            ->getData(Argument::type('int'))
            ->will(
                function ($args) use ($data) {
                    if (in_array($args[0], [1, 2])) {
                        return $data[($args[0] - 1)];
                    }

                    return false;
                }
            );

        $dataEntity = new UserGroupEntity();
        /** @var DataAdapterInterface $defaultAdapterInstance */
        $defaultAdapterInstance = $this->defaultAdapter->reveal();
        $storage = new UserGroupStorage($defaultAdapterInstance, $dataEntity);


        /** @var UserGroupEntity $actualResult */
        $actualResult = $storage->getUserGroupById(1);
        $this->assertInstanceOf(UserGroupEntity::class, $actualResult);
        $this->assertFalse($dataEntity === $actualResult);
        $this->assertEquals($data[0]['title'], $actualResult->getTitle());
        $this->assertEquals($data[0]['description'], $actualResult->getDescription());
        $this->assertTrue($actualResult->getReadOnly());

        /** @var UserGroupEntity $actualResult */
        $actualResult = $storage->getUserGroupById(2);
        $this->assertInstanceOf(UserGroupEntity::class, $actualResult);
        $this->assertFalse($dataEntity === $actualResult);
        $this->assertEquals($data[1]['title'], $actualResult->getTitle());
        $this->assertEquals($data[1]['description'], $actualResult->getDescription());
        $this->assertFalse($actualResult->getReadOnly());

        /** @var bool $actualResult */
        $actualResult = $storage->getUserGroupById(3);
        $this->assertFalse($actualResult);
    }
}