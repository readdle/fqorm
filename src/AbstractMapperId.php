<?php
declare(strict_types=1);

namespace Readdle\Database\ORM;

abstract class AbstractMapperId extends AbstractMapper
{
    protected const ID = 'id'; // hopefully won't need to override this
    protected FieldMap $idFieldMap;

    /**
     * @inheritDoc
     */
    protected function createFieldMappingUnique(): array
    {
        $this->idFieldMap = FieldMap::idMap(static::ID);
        return [$this->idFieldMap];
    }

    protected function untypedFindById(string $id): ?EntityInterface
    {
        return $this->untypedFindByOne([static::ID => $id]);
    }
}
