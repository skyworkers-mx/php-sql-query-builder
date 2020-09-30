<?php

namespace NilPortugues\Sql\QueryBuilder\Syntax\Columns;

class ColumnAll
{
  private string $table_alias;

  public function __construct(string $table_alias = "")
  {
    $this->table_alias = $table_alias;
  }

  public function getTable(): string
  {
    return $this->table_alias;
  }
}
