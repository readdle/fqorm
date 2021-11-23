<?php
declare(strict_types=1);

namespace Readdle\Database\ORM;

use DateTime;
use DateTimeInterface;
use Exception;

class FieldMap
{
    public string $sqlName;
    public string $propName;

    /**
     * @var ?callable
     */
    public $mapToObject;

    /**
     * @var ?callable
     */
    public $mapToDatabase;

    public bool $useDefault = false;
    public bool $alwaysReload = false;
    public bool $isAutoincrement = false;

    private const MYSQL_DATETIME_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        string $sqlName,
        string $propName = '',
        ?callable $mapToObject = null,
        ?callable $mapToDatabase = null
    ) {
        if ($propName === '') {
            $propName = $sqlName;
        }

        $this->sqlName = $sqlName;
        $this->propName = $propName;
        $this->mapToObject = $mapToObject;
        $this->mapToDatabase = $mapToDatabase;
    }

    /**
     * Syntax shortcut for mapping for id field
     *
     * @param string $idFieldName
     * @return FieldMap
     */
    public static function idMap(string $idFieldName): FieldMap
    {
        $obj = new FieldMap($idFieldName, '');
        $obj->isAutoincrement = true;
        return $obj;
    }

    /**
     * Crates a mapping between DATETIME field and php \DateTime class
     *
     * @param string $sqlName
     * @param string $propName
     * @param bool $useDefault
     * @param bool $alwaysReload
     * @return static
     */
    public static function datetimeMap(
        string $sqlName,
        string $propName = '',
        bool $useDefault = true,
        bool $alwaysReload = false
    ): FieldMap {
        $obj = new FieldMap($sqlName, $propName, [static::class, 'SQLToDateTime'], [static::class, 'dateTimeToSQL']);
        $obj->useDefault = $useDefault;
        $obj->alwaysReload = $alwaysReload;
        return $obj;
    }

    /**
     * Helper function for datetimeMap() mapping
     *
     * @param DateTimeInterface|null $dateTime
     * @return string|null
     * @noinspection PhpUnused
     */
    public static function dateTimeToSQL(?DateTimeInterface $dateTime): ?string
    {
        if ($dateTime === null) {
            return null;
        }

        return $dateTime->format(FieldMap::MYSQL_DATETIME_FORMAT);
    }

    /**
     * Helper function for datetimeMap() mapping
     *
     * @param string $dateTime
     * @return ?DateTime
     * @noinspection PhpUnused
     */
    public static function SQLToDateTime(string $dateTime): ?DateTime
    {
        try {
            $retval = DateTime::createFromFormat(FieldMap::MYSQL_DATETIME_FORMAT, $dateTime);
            return $retval === false ? null : $retval;
        } catch (Exception $e) {
            return null;
        }
    }
}
