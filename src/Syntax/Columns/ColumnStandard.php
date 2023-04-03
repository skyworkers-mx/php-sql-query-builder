<?php

namespace NilPortugues\Sql\QueryBuilder\Syntax\Columns;

use NilPortugues\Sql\QueryBuilder\Manipulation\QueryException;

class ColumnStandard extends Column
{

  private string $column;

  public function __construct(string $column, string $alias)
  {
    if ($column == Column::ALL && $alias) {
      throw new QueryException("Can't use alias because column name is ALL (*)");
    }
    $this->column = $column;
    $this->alias = $alias;
  }

  public function getColumn(): string
  {
    return $this->column;
  }
}
