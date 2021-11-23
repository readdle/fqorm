<?php
declare(strict_types=1);

namespace Readdle\Database\ORM;

class SQLWriter
{
    private string $tableName;
    private int $idx;

    /**
     * SQLWriter constructor.
     * @param string $tableName
     */
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
    }

    public function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    private function where(array $where): SQLStringArgs
    {
        if (empty($where)) {
            return new SQLStringArgs('', []);
        }
        $sql = ' WHERE ';
        $args = [];
        foreach ($where as $key => $value) {
            $this->idx++;

            $argName = ':arg'.$this->idx;
            $args[$argName] = $value;

            $sql .= "`{$key}` = {$argName} AND";
        }
        $sql = substr($sql, 0, -4); // -4 is -strlen(' AND')
        return new SQLStringArgs($sql, $args);
    }

    private function set(array $set): SQLStringArgs
    {
        $sql = ' SET ';
        $args = [];
        foreach ($set as $key => $value) {
            $this->idx++;

            $argName = ':arg'.$this->idx;
            $args[$argName] = $value;
            $sql .= "`{$key}` = {$argName}, ";
        }
        $sql = substr($sql, 0, -2); // -4 is -strlen(', ')
        return new SQLStringArgs($sql, $args);
    }


    private function insertFields(array $set): SQLStringArgs
    {
        $sqlPart1 = '(';
        $sqlPart2 = 'VALUES (';

        $args = [];
        foreach ($set as $key => $value) {
            $this->idx++;

            $argName = ':arg'.$this->idx;
            $args[$argName] = $value;
            $sqlPart1 .= "`{$key}`, ";
            $sqlPart2 .= "{$argName}, ";
        }
        $sqlPart1 = substr($sqlPart1, 0, -2); // -4 is -strlen(', ')
        $sqlPart2 = substr($sqlPart2, 0, -2); // -4 is -strlen(', ')
        $sqlPart1 .= ')';
        $sqlPart2 .= ')';

        return new SQLStringArgs($sqlPart1.' '.$sqlPart2, $args);
    }



    /**
     * @param FieldMap[] $fields
     * @param array $where
     * @param string $sqlAfterWhere
     * @return SQLStringArgs
     */
    public function select(array $fields, array $where, string $sqlAfterWhere = ''): SQLStringArgs
    {
        $sql = '';
        foreach ($fields as $field) {
            $sql .= '`'.$field->sqlName.'`, ';
        }
        $sql = substr($sql, 0, -2); // -4 is -strlen(', ')
        return $this->selectRaw($sql, $where, $sqlAfterWhere);
    }


    /**
     * @param string $rawSQL
     * @param array $where
     * @param string $sqlAfterWhere
     * @return SQLStringArgs
     */
    public function selectRaw(string $rawSQL, array $where, string $sqlAfterWhere = ''): SQLStringArgs
    {
        if ($sqlAfterWhere !== '') {
            $sqlAfterWhere = ' '.$sqlAfterWhere;
        }
        $this->resetArgsIdx();
        $sql = 'SELECT ';
        $sql .= $rawSQL;
        $sql .= ' FROM '.$this->tableName.' ';
        if (empty($where)) {
            return new SQLStringArgs($sql.$sqlAfterWhere, []);
        }

        $whereCode = $this->where($where);
        return new SQLStringArgs($sql.$whereCode->query.$sqlAfterWhere, $whereCode->args);
    }


    public function update(array $data, array $where): SQLStringArgs
    {
        $this->resetArgsIdx();

        $setSql = $this->set($data);
        $whereSql = $this->where($where);

        $sql = 'UPDATE '.$this->tableName.$setSql->query.$whereSql->query;

        return new SQLStringArgs($sql, array_merge($setSql->args, $whereSql->args));
    }

    public function delete(array $where): SQLStringArgs
    {
        $this->resetArgsIdx();

        $whereSql = $this->where($where);

        $sql = 'DELETE '.'FROM '.$this->tableName.$whereSql->query;

        return new SQLStringArgs($sql, $whereSql->args);
    }

    public function insert(array $data, bool $replace = false): SQLStringArgs
    {
        $this->resetArgsIdx();
        $setSql = $this->insertFields($data);

        $sql = 'INSERT';
        if ($replace) {
            $sql = 'REPLACE';
        }

        $sql .= ' INTO '.$this->tableName.$setSql->query;

        return new SQLStringArgs($sql, $setSql->args);
    }


    private function resetArgsIdx(): void
    {
        $this->idx = 0;
    }
}
