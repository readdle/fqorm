<?php
declare(strict_types=1);

namespace Readdle\Database\ORM\Tests;

use DateTime;
use Readdle\Database\ORM\EntityInterface;

class ExampleIdEntity implements EntityInterface
{
    public ?int $id = null;
    public string $field1;
    public string $field2;
    public ?DateTime $dateTime;

    public function __construct(string $field1 = '', string $field2 = '', ?DateTime $dateTime = null)
    {
        $this->field1 = $field1;
        $this->field2 = $field2;
        $this->dateTime = $dateTime;
    }
}
