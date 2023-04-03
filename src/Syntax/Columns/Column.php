<?php

namespace NilPortugues\Sql\QueryBuilder\Syntax\Columns;

class Column
{
  const ALL = "*";
  public string $alias = "";

  public function getAlias()
  {
    return $this->alias;
  }
}
