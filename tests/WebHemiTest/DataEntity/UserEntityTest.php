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
namespace WebHemiTest\DataEntity;

use DateTime;
use WebHemi\DataEntity\DataEntityInterface;
use WebHemi\DataEntity\User\UserEntity;
use PHPUnit_Framework_TestCase as TestCase;

/**
 * Class UserEntityTest.
 */
class UserEntityTest extends TestCase
{
    /** @var string */
    private $testTime = '2016-04-26 23:21:00';

    /**
     * Tests if the UserEntity implements the DataEntityInterface.
     */
    public function testInstance()
    {
        $entity = new UserEntity();

        $this->assertInstanceOf(DataEntityInterface::class, $entity);
    }

    /**
     * Data provider for the tests.
     *
     * @return array
     */
    public function dataProvider()
    {
        $dateTest = new DateTime($this->testTime);

        return [
            ['userId', 1, 1, false],
            ['userId', 'someId', 'someId', false],
            ['userName', 'someName', 'someName', false],
            ['email', 'someEmail', 'someEmail', false],
            ['password', 'somePassword', 'somePassword', false],
            ['hash', 'someHash', 'someHash', false],
            ['lastIp', 'someIp', 'someIp', false],
            ['registerIp', 'someIp', 'someIp', false],
            ['isActive', 1, true, true],
            ['isActive', null, false, true],
            ['isActive', 'yes', true, true],
            ['isEnabled', 'someValue', true, true],
            ['isEnabled', 'no', true, true],
            ['isEnabled', 0, false, true],
            ['timeLogin', $dateTest, $dateTest, true],
            ['timeRegister', $dateTest, $dateTest, true],
        ];
    }

    /**
     * Tests if the UserEntity instance has any property preset.
     *
     * @param string $attribute
     *
     * @dataProvider dataProvider
     */
    public function testNoInitValues($attribute)
    {
        $entity = new UserEntity();

        $this->assertClassHasAttribute($attribute, UserEntity::class);
        $this->assertAttributeEmpty($attribute, $entity);
    }

    /**
     * Tests if the UserEntity has a specific setter method and it sets the value with the correct type.
     *
     * @param string $attribute
     * @param mixed  $parameter
     * @param mixed  $expectedData
     * @param bool   $typeCheck
     *
     * @dataProvider dataProvider
     */
    public function testSetters($attribute, $parameter, $expectedData, $typeCheck)
    {
        $entity = new UserEntity();
        $method = 'set' . ucfirst(preg_replace('/^is/', '', $attribute));

        $this->assertTrue(method_exists($entity, $method));

        $entity->{$method}($parameter);
        $this->assertAttributeEquals($expectedData, $attribute, $entity);

        if ($typeCheck) {
            if (is_bool($expectedData)) {
                $this->assertAttributeInternalType('boolean', $attribute, $entity);
            } else {
                $this->assertAttributeInstanceOf(DateTime::class, $attribute, $entity);
            }
        }
    }

    /**
     * Tests if the UserEntity has a specific getter method and it gets the value with the correct type.
     *
     * @param string $attribute
     * @param mixed  $parameter
     * @param mixed  $expectedData
     * @param bool   $typeCheck
     *
     * @dataProvider dataProvider
     */
    public function testGetters($attribute, $parameter, $expectedData, $typeCheck)
    {
        $entity = new UserEntity();
        $methodName = ucfirst(preg_replace('/^is/', '', $attribute));
        $setMethod = 'set' . $methodName;
        $getMethod = 'get' . $methodName;

        $this->assertTrue(method_exists($entity, $setMethod));
        $this->assertTrue(method_exists($entity, $getMethod));

        $entity->{$setMethod}($parameter);
        $actualData = $entity->{$getMethod}();

        $this->assertEquals($expectedData, $actualData);

        if ($typeCheck) {
            if (is_bool($expectedData)) {
                $this->assertInternalType('boolean', $actualData);
            } else {
                $this->assertInstanceOf(DateTime::class, $actualData);
            }
        }
    }
}