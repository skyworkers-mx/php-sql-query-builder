<?php

namespace NilPortugues\Sql\QueryBuilder\Syntax\Columns;

class ColumnFunction extends Column
{
  private string $function;
  private array $arguments;

  public function __construct(string $function, string $alias, array $arguments)
  {
    $this->function = $function;
    $this->arguments = $arguments;
    $this->alias = $alias;
  }

  public function getFunction(): string
  {
    return $this->function;
  }

  public function getArguments(): array
  {
    return $this->arguments;
  }
}
