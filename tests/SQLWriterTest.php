<?php
declare(strict_types=1);

namespace Readdle\Database\ORM\Tests;

use PHPUnit\Framework\TestCase;
use Readdle\Database\ORM\FieldMap;
use Readdle\Database\ORM\SQLWriter;


class SQLWriterTest extends TestCase
{
    public function testSelect()
    {
        $sqlWriter = new SQLWriter('test');
        $result = $sqlWriter->select(
            [new FieldMap('field1', 'prop1'),
             new FieldMap('field2', 'prop2')],
            ['field1' => 42]
        );

        $this->assertEquals(/** @lang text */'SELECT `field1`, `field2` FROM test  WHERE `field1` = :arg1',
            $result->query
        );
        $this->assertEquals([':arg1' => 42], $result->args);
    }

    public function testDelete()
    {
        $sqlWriter = new SQLWriter('test');
        $result = $sqlWriter->delete(['field1' => 'test']);

        $this->assertEquals(/** @lang text */'DELETE FROM test WHERE `field1` = :arg1',
            $result->query
        );
        $this->assertEquals([':arg1' => 'test'], $result->args);
    }

    public function testUpdate()
    {
        $sqlWriter = new SQLWriter('test');
        $result = $sqlWriter->update(['field2' => 11], ['field1' => 'test']);

        $this->assertEquals(/** @lang text */'UPDATE test SET `field2` = :arg1 WHERE `field1` = :arg2',
            $result->query
        );
        $this->assertEquals([':arg1' => '11', ':arg2' => 'test'], $result->args);
    }

    public function testInsert()
    {
        $sqlWriter = new SQLWriter('test');
        $result = $sqlWriter->insert(['field1' => 42, 'field2' => 'test']);

        $this->assertEquals(/** @lang text */'INSERT INTO test(`field1`, `field2`) VALUES (:arg1, :arg2)',
            $result->query
        );
        $this->assertEquals([':arg1' => 42, ':arg2' => 'test'], $result->args);
    }
}
