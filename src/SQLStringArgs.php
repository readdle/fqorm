<?php
declare(strict_types=1);

namespace Readdle\Database\ORM;

class SQLStringArgs
{
    public string $query;
    public array $args;

    public function __construct(string $query, array $args)
    {
        $this->query = $query;
        $this->args = $args;
    }
}
