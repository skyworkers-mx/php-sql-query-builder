<?php

namespace NilPortugues\Sql\QueryBuilder\Syntax\Columns;

class ColumnValue extends Column
{
  private string $value;

  public function __construct(string $value, string $alias)
  {
    $this->value = $value;
    $this->alias = $alias;
  }

  public function getValue(): string
  {
    return $this->value;
  }
}
