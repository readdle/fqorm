<?php
declare(strict_types=1);

namespace Readdle\Database\ORM\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use Readdle\Database\FQDB;
use Readdle\License\App\DBManager;

class MapperIdTest extends TestCase
{
    /**
     * @var FQDB
     */
    protected static $fqdb;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$fqdb = \Readdle\Database\FQDBProvider::dbWithDSN('sqlite::memory:');
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::$fqdb->execute('CREATE TABLE IF NOT EXISTS `unit_test_idtest` (
                    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                    `field1` VARCHAR(100) NOT NULL,
                    `field2` VARCHAR(100) NOT NULL,
                    `dateTime` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                )');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // this table exists only during test run, hence annotation with @lang text
        static::$fqdb->execute(/** @lang text */'DROP TABLE `unit_test_idtest`');
    }

    public function testBasicFunctionality() : void
    {
        $entity = new ExampleIdEntity('b', 'c');


        $mapper = new ExampleIdMapper(static::$fqdb);
        $mapper->save($entity);


        $another = new ExampleIdEntity('a', 'b');
        $mapper->save($another);

        $this->assertEquals(1, $entity->id);

        $e2 = $mapper->findById('1');
        $this->assertEquals($entity, $e2);

        $entity->field2 = 'rest';
        $mapper->save($entity);

        $e2 = $mapper->findById('1');
        $this->assertEquals($entity, $e2);

        $mapper->delete($entity);

        $this->assertEquals($another, $mapper->findById('2'));
        $mapper->delete($another);

        $this->assertEquals(0, static::$fqdb->queryValue(/** @lang text */
            'SELECT COUNT(*) FROM unit_test_idtest'
        ));
    }


    public function testDatetime(): void
    {
        $entity = new ExampleIdEntity('a', 'b', new DateTime('@100000'));

        $mapper = new ExampleIdMapper(static::$fqdb);
        $mapper->save($entity);

        $e2 = $mapper->findById('1');
        if ($e2->dateTime === null) {
            $this->fail('dateTime is null'); // for scrutenizer
        } else {
            $this->assertEquals('1970-01-02T03:46:40+0000', $e2->dateTime->format(DateTime::ISO8601));
        }
    }
}
