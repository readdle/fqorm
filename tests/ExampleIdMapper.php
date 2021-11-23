<?php
declare(strict_types=1);

namespace Readdle\Database\ORM\Tests;

use Readdle\Database\ORM\AbstractMapperId;
use Readdle\Database\ORM\FieldMap;

class ExampleIdMapper extends AbstractMapperId
{
    protected static function mapperTableName(): string
    {
        return 'unit_test_idtest';
    }

    protected static function mapperEntityClass(): string
    {
        return ExampleIdEntity::class;
    }

    protected function createFieldMapping(): array
    {
        return [
            $this->idFieldMap,
            new FieldMap('field1'),
            new FieldMap('field2'),
            FieldMap::datetimeMap('dateTime'),
        ];
    }

    public function save(ExampleIdEntity $entity): void
    {
        parent::untypedSave($entity);
    }

    public function delete(ExampleIdEntity $entity): void
    {
        parent::untypedDelete($entity);
    }

    public function findById($id): ?ExampleIdEntity
    {
        $obj = parent::untypedFindById($id);
        if ($obj instanceof ExampleIdEntity) {
            return $obj; // always true, to silence phpStorm / Scrutenizer
        }
        return null;
    }
}
