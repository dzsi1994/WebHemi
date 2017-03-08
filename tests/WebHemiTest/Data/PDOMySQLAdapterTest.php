<?php
/**
 * WebHemi.
 *
 * PHP version 7.1
 *
 * @copyright 2012 - 2017 Gixx-web (http://www.gixx-web.com)
 * @license   https://opensource.org/licenses/MIT The MIT License (MIT)
 *
 * @link      http://www.gixx-web.com
 */
namespace WebHemiTest\Data;

use InvalidArgumentException;
use PDO;
use RuntimeException;
use WebHemi\Data\Connector\PDO\MySQL\ConnectorAdapter as MySQLAdapter;
use WebHemi\Data\Connector\PDO\MySQL\DriverAdapter as MySQLDriver;
use WebHemi\Data\ConnectorInterface as DataAdapterInterface;
use WebHemi\Data\DriverInterface as DataDriverInterface;
use WebHemiTest\TestExtension\AssertArraysAreSimilarTrait as AssertTrait;
use WebHemiTest\TestExtension\InvokePrivateMethodTrait;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\SkippedTestError;

/**
 * Class MySQLAdapterTest.
 */
class PDOMySQLAdapterTest extends TestCase
{
    /** @var DataDriverInterface */
    protected $dataDriver;

    use AssertTrait;
    use InvokePrivateMethodTrait;

    /**
     * Check requirements - also checks SQLite availability.
     */
    protected function checkRequirements()
    {
        if (!extension_loaded('pdo_sqlite')) {
            throw new SkippedTestError('No SQLite Available');
        }

        parent::checkRequirements();
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     */
    public function setUp()
    {
        parent::setUp();
        $databaseFile = realpath(__DIR__ . '/../../../build/webhemi_schema.sqlite3');
        // The trick is that the MySQLDriver is a simple extension to the PDO class, so it can be used for the SQLite
        // as well without any issue.
        $this->dataDriver = new MySQLDriver('sqlite:' . $databaseFile);
    }

    /**
     * Tests constructor.
     *
     * @throws InvalidArgumentException
     */
    public function testConstructor()
    {
        $adapter = new MySQLAdapter($this->dataDriver);
        $this->assertInstanceOf(DataAdapterInterface::class, $adapter);
        $this->assertAttributeEmpty('dataGroup', $adapter);
        $this->assertAttributeEmpty('idKey', $adapter);

        /** @var DataDriverInterface $fakeDriver */
        $fakeDriver = $this->prophesize(DataDriverInterface::class);

        $this->expectException(InvalidArgumentException::class);
        new MySQLAdapter($fakeDriver->reveal());
    }

    /**
     * Tests the getDataDriver method.
     */
    public function testGetDataDriver()
    {
        $adapter = new MySQLAdapter($this->dataDriver);
        $this->assertInstanceOf(DataAdapterInterface::class, $adapter);

        $actualStorage = $adapter->getDataDriver();
        $this->assertInstanceOf(PDO::class, $actualStorage);
        $this->assertTrue($this->dataDriver === $actualStorage);
    }

    /**
     * Tests the setDataGroup method.
     *
     * @throws RuntimeException
     */
    public function testSetDataGroup()
    {
        $adapter = new MySQLAdapter($this->dataDriver);
        $this->assertInstanceOf(DataAdapterInterface::class, $adapter);

        $result = $adapter->setDataGroup('webhemi_user');
        $this->assertInstanceOf(DataAdapterInterface::class, $result);
        $this->assertTrue($adapter === $result);
    }

    /**
     * Tests setIdKey method.
     *
     * @throws RuntimeException
     */
    public function testSetIdKey()
    {
        $adapter = new MySQLAdapter($this->dataDriver);
        $this->assertInstanceOf(DataAdapterInterface::class, $adapter);

        $result = $adapter->setIdKey('id_user');
        $this->assertInstanceOf(DataAdapterInterface::class, $result);
        $this->assertTrue($adapter === $result);
    }

    /**
     * Data provider for the Query test.
     *
     * @return array
     */
    public function sqlQueryDataProvider()
    {
        return [
            [
                [],
                'aTable',
                1,
                5,
                'SELECT * FROM aTable LIMIT 1 OFFSET 5',
                []
            ],
            [
                ['A' => 5],
                'bTable',
                11,
                20,
                'SELECT * FROM bTable WHERE A = ? LIMIT 11 OFFSET 20',
                [5]
            ],
            [
                ['A' => 10, 'B LIKE ?' => 'someData%'],
                'cTable',
                null,
                null,
                'SELECT * FROM cTable WHERE A = ? AND B LIKE ? LIMIT '.PHP_INT_MAX.' OFFSET 0',
                [10, 'someData%']
            ],
            [
                ['A' => 10, 'B LIKE' => 'someData%'],
                'cTable',
                null,
                null,
                'SELECT * FROM cTable WHERE A = ? AND B LIKE ? LIMIT '.PHP_INT_MAX.' OFFSET 0',
                [10, 'someData%']
            ],
            [
                ['A' => 10, 'B' => 'someData%'],
                'cTable',
                null,
                null,
                'SELECT * FROM cTable WHERE A = ? AND B LIKE ? LIMIT '.PHP_INT_MAX.' OFFSET 0',
                [10, 'someData%']
            ],
            [
                ['A' => 10, 'B IN (?)' => [1,2,3]],
                'cTable',
                3,
                0,
                'SELECT * FROM cTable WHERE A = ? AND B IN (?,?,?) LIMIT 3 OFFSET 0',
                [10, 1, 2, 3]
            ],
            [
                ['A' => 10, 'B IN ?' => [1,2,3]],
                'cTable',
                3,
                0,
                'SELECT * FROM cTable WHERE A = ? AND B IN (?,?,?) LIMIT 3 OFFSET 0',
                [10, 1, 2, 3]
            ],
            [
                ['A' => 10, 'B' => [1,2,3]],
                'cTable',
                3,
                0,
                'SELECT * FROM cTable WHERE A = ? AND B IN (?,?,?) LIMIT 3 OFFSET 0',
                [10, 1, 2, 3]
            ]
        ];
    }

    /**
     * Tests WHERE expression generator.
     *
     * @param array  $expression
     * @param string $dataGroup
     * @param int    $limit
     * @param int    $offset
     * @param string $expectedQuery
     * @param array  $expectedQueryBind
     *
     * @dataProvider sqlQueryDataProvider
     */
    public function testGetQueryForExpression(
        $expression,
        $dataGroup,
        $limit,
        $offset,
        $expectedQuery,
        $expectedQueryBind
    ) {
        $queryBind = [];

        $adapter = new MySQLAdapter($this->dataDriver);
        $adapter->setDataGroup($dataGroup);

        if (!is_null($limit)) {
            $resultQuery = $this->invokePrivateMethod(
                $adapter,
                'getSelectQueryForExpression',
                [$expression, &$queryBind, $limit, $offset]
            );
        } else {
            $resultQuery = $this->invokePrivateMethod(
                $adapter,
                'getSelectQueryForExpression',
                [$expression, &$queryBind]
            );
        }

        $this->assertEquals($expectedQuery, $resultQuery);
        $this->assertArraysAreSimilar($expectedQueryBind, $queryBind);
    }

    /**
     * Data provider for the WHERE expression test.
     *
     * @return array
     */
    public function whereExpressionDataProvider()
    {
        return [
            [[], '', []],
            [['A' => 5], ' WHERE A = ?', [5]],
            [['A' => 10, 'B LIKE ?' => 'someData%'], ' WHERE A = ? AND B LIKE ?', [10, 'someData%']],
            [['A' => 10, 'B' => [1,2,3]], ' WHERE A = ? AND B IN (?,?,?)', [10, 1, 2, 3]],
        ];
    }

    /**
     * Tests WHERE expression generator.
     *
     * @param array  $expression
     * @param string $expectedExpression
     * @param array  $expectedQueryBind
     *
     * @dataProvider whereExpressionDataProvider
     */
    public function testGetWhereExpression($expression, $expectedExpression, $expectedQueryBind)
    {
        $queryBind = [];

        $adapter = new MySQLAdapter($this->dataDriver);
        $resultExpression = $this->invokePrivateMethod($adapter, 'getWhereExpression', [$expression, &$queryBind]);

        $this->assertEquals($expectedExpression, $resultExpression);
        $this->assertArraysAreSimilar($expectedQueryBind, $queryBind);
    }
}