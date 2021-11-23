<?php
declare(strict_types=1);

namespace Readdle\Database\ORM\Tests;

use Readdle\Database\ORM\AbstractMapper;
use Readdle\Database\ORM\FieldMap;

class ExampleMapper extends AbstractMapper
{
    protected static function mapperTableName(): string
    {
        return 'unit_test_test';
    }

    protected static function mapperEntityClass(): string
    {
        return ExampleEntity::class;
    }

    protected function createFieldMapping(): array
    {
        return array_merge($this->fieldsMapUnique, [
            new FieldMap('field1', 'field1Prop'),
            new FieldMap(
                'field2',
                'field2Prop',
                function ($x) {
                    return json_decode($x, true);
                },
                function ($x) {
                    return json_encode($x);
                }
            ),
        ]);
    }

    protected function createFieldMappingUnique(): array
    {
        return $this->fieldsMapUnique = [
            new FieldMap('uniqid', 'uniqidProp')
        ];
    }

    public function findByUnique(string $unique): ?ExampleEntity
    {
        $q = parent::findByWhere(['uniqid' => $unique]);
        if ($q === []) {
            return null;
        }
        return $q[0];
    }

    /**
     * @param array $conditions
     * @return ExampleEntity[]
     */
    public function findByWhere(array $conditions): array
    {
        return parent::findByWhere($conditions);
    }

    public function delete(ExampleEntity $entity): int
    {
        return parent::untypedDelete($entity);
    }

    public function save(ExampleEntity $entity): void
    {
        parent::untypedSave($entity);
    }
}
