<?php

namespace NilPortugues\Sql\QueryBuilder\Syntax\Columns;

class ColumnCustom extends Column
{

  private string $column;

  public function __construct(string $alias, string $column)
  {
    $this->alias = $alias;
    $this->column = $column;
  }

  public function getColumn(): string
  {
    return $this->column;
  }
}
