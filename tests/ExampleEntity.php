<?php
declare(strict_types=1);

namespace Readdle\Database\ORM\Tests;

use Readdle\Database\ORM\EntityInterface;

class ExampleEntity implements EntityInterface
{
    public string $uniqidProp;
    public string $field1Prop;
    public array $field2Prop;
}
