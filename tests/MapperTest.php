<?php
declare(strict_types=1);

namespace Readdle\Database\ORM\Tests;

use PHPUnit\Framework\TestCase;
use Readdle\Database\FQDB;
use Readdle\License\App\DBManager;

class MapperTest extends TestCase
{
    protected static FQDB $fqdb;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        static::$fqdb = \Readdle\Database\FQDBProvider::dbWithDSN('sqlite::memory:');
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::$fqdb->execute('CREATE TABLE IF NOT EXISTS `unit_test_test` (
                    `uniqid` VARCHAR(64) NOT NULL,
                    `field1` VARCHAR(100) NOT NULL,
                    `field2` VARCHAR(100) NOT NULL
                )');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // this table exists only during test run, hence annotation with @lang text
        static::$fqdb->execute(/** @lang text */'DROP TABLE `unit_test_test`');
    }

    public function testBasicFunctionality()
    {
        $mapper = new ExampleMapper(static::$fqdb);

        $e1 = new ExampleEntity();
        $e1->uniqidProp = uniqid();
        $e1->field1Prop = 'f1';
        $twoElArray = ['array1', 'array2'];
        $e1->field2Prop = $twoElArray;

        $mapper->save($e1);

        $json = static::$fqdb->queryValue(/** @lang text */
            'SELECT field2 FROM unit_test_test WHERE uniqid=:u',
            [':u' => $e1->uniqidProp]
        );
        $this->assertEquals(json_encode($twoElArray), $json);


        $e2 = $mapper->findByUnique($e1->uniqidProp);

        $this->assertEquals($e1, $e2);

        $someArray = ['array1', 'array2', 'array3'];
        $e1->field2Prop = ['array1', 'array2', 'array3'];
        $mapper->save($e1);

        $e2 = $mapper->findByUnique($e1->uniqidProp);

        $this->assertEquals($someArray, $e2->field2Prop);

        $mapper->delete($e1);
        $afterDelete = $mapper->findByUnique($e1->uniqidProp);
        $this->assertNull($afterDelete);
    }
}
