<?php
declare(strict_types=1);
namespace Readdle\Database\ORM;

use LogicException;
use Readdle\Database\FQDB;

abstract class AbstractMapper
{
    /**
     * Returns the name of the table to map.
     * @return string
     */
    abstract protected static function mapperTableName(): string;

    /**
     * Returns the class of the object to map. Use ClassName::class notation.
     * @return string
     */
    abstract protected static function mapperEntityClass(): string;

    /**
     * Configures fieldsMapAll property with all necessary table fields.
     * This method is called after createFieldMappingUnique().
     * It must return all fields, including fields returned by createFieldMappingUnique().
     * @return FieldMap[]
     */
    abstract protected function createFieldMapping(): array;

    /**
     * Configures fieldsMapUnique property with unique fields to identify table record.
     * This method is called before createFieldMapping()
     * @return FieldMap[]
     */
    abstract protected function createFieldMappingUnique(): array;

    /** @var FieldMap[] */
    protected array $fieldsMapAll;
    /** @var FieldMap[] */
    protected array $fieldsMapUnique;
    protected FQDB $fqdb;
    protected SQLWriter $sqlWriter;
    protected bool $willNeedReloadAfterSave = false;


    public function __construct(FQDB $fqdb)
    {
        $this->fqdb = $fqdb;
        $this->sqlWriter = new SQLWriter(static::mapperTableName());
        $this->fieldsMapUnique = $this->createFieldMappingUnique();
        $this->fieldsMapAll = $this->createFieldMapping();
        foreach ($this->fieldsMapUnique as $field) {
            if (!in_array($field, $this->fieldsMapAll)) {
                throw new LogicException('fieldMapAll must contain fields from fieldMapUnique');
            }
        }
    }

    protected function newObject(): EntityInterface
    {
        $entityClass = static::mapperEntityClass();
        return new $entityClass;
    }

    protected function mapToObject(EntityInterface $obj, array $dbValues): EntityInterface
    {
        foreach ($this->fieldsMapAll as $field) {
            $value = $dbValues[$field->sqlName];
            if ($field->mapToObject !== null) {
                $value = ($field->mapToObject)($value);
            }
            $obj->{$field->propName} = $value;
        }
        return $obj;
    }

    /**
     * @param EntityInterface $obj
     * @param FieldMap[] $fieldsInfo
     * @return array
     */
    protected function mapToDatabase(EntityInterface $obj, array $fieldsInfo): array
    {
        $dbArray = [];
        foreach ($fieldsInfo as $field) {
            $value = $obj->{$field->propName};

            if (($value === null && $field->useDefault) || $field->alwaysReload) {
                $this->willNeedReloadAfterSave = true;
                continue;
            }

            if ($field->mapToDatabase !== null) {
                $value = ($field->mapToDatabase)($value);
            }
            $dbArray[$field->sqlName] = $value;
        }
        return $dbArray;
    }

    // OVERRIDE with concrete implementation
    public function untypedFindByOne(array $conditions): ?EntityInterface
    {
        $sql = $this->sqlWriter->select($this->fieldsMapAll, $conditions, 'LIMIT 1');
        $resultRow = $this->fqdb->queryAssoc($sql->query, $sql->args);
        if ($resultRow === false) {
            return null;
        }
        return $this->mapToObject($this->newObject(), $resultRow);
    }

    public function findByWhere(array $conditions): array
    {
        $sql = $this->sqlWriter->select($this->fieldsMapAll, $conditions);
        $resultTable = $this->fqdb->queryTable($sql->query, $sql->args);

        $objs = [];
        foreach ($resultTable as $resultRow) {
            $obj = $this->newObject();
            $this->mapToObject($obj, $resultRow);
            $objs[] = $obj;
        }
        return $objs;
    }

    // OVERRIDE with concrete implementation
    protected function untypedSave(EntityInterface $entity): void
    {
        $this->willNeedReloadAfterSave = false;

        $set = $this->mapToDatabase($entity, $this->fieldsMapAll);
        $conditions = $this->mapToDatabase($entity, $this->fieldsMapUnique);

        $sql = $this->sqlWriter->update($set, $conditions);
        $affected = $this->fqdb->update($sql->query, $sql->args);

        if ($affected == 0) {
            $sql = $this->sqlWriter->selectRaw('COUNT(*)', $conditions);
            $count = $this->fqdb->queryValue($sql->query, $sql->args);

            if ($count == 0) {
                $sql = $this->sqlWriter->insert($set);
                $insertResult = $this->fqdb->insert($sql->query, $sql->args);
                $this->updateAutoincrement($entity, $insertResult);
                $conditions = $this->mapToDatabase($entity, $this->fieldsMapUnique);
            }
        }

        $this->reloadAfterSave($entity, $conditions);
    }

    protected function updateAutoincrement(EntityInterface $entity, string $insertResult): void
    {
        if (count($this->fieldsMapUnique) != 1 || $this->fieldsMapUnique[0]->isAutoincrement === false) {
            return;
        }
        $idMap = $this->fieldsMapUnique[0];
        $entity->{$idMap->propName} = intval($insertResult);
    }

    protected function reloadAfterSave(EntityInterface $entity, array $conditions): void
    {
        if (!$this->willNeedReloadAfterSave) {
            return;
        }
        $sql = $this->sqlWriter->select($this->fieldsMapAll, $conditions);
        $resultRow = $this->fqdb->queryAssoc($sql->query, $sql->args);
        if ($resultRow === false) {
            throw new LogicException('ORM cannot reload class after save');
        }
        $this->mapToObject($entity, $resultRow);
    }

    // OVERRIDE with concrete implementation
    protected function untypedDelete(EntityInterface $entity): int
    {
        $where = $this->mapToDatabase($entity, $this->fieldsMapUnique);
        $sql = $this->sqlWriter->delete($where);
        return $this->fqdb->delete($sql->query, $sql->args);
    }
}
