<?php

namespace NilPortugues\Sql\QueryBuilder\Manipulation;

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/25/14
 * Time: 12:12 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use NilPortugues\Sql\QueryBuilder\Syntax\Column;
use NilPortugues\Sql\QueryBuilder\Syntax\OrderBy;

use NilPortugues\Sql\QueryBuilder\Syntax\Columns\{ColumnAll, ColumnCustom, ColumnStandard, ColumnFunction, ColumnValue};

/**
 * Class ColumnQuery.
 */
class ColumnQuery
{
    /**
     * @var array
     */
    protected array $columns = [];

    protected bool $select_all = false;

    /**
     * @var bool
     */
    protected $isCount = false;

    /**
     * @var Select
     */
    protected $select;

    /**
     * @var JoinQuery
     */
    protected $joinQuery;

    protected $partitions = [];

    /**
     * @param Select    $select
     * @param JoinQuery $joinQuery
     * @param array     $columns
     */
    public function __construct(Select $select, JoinQuery $joinQuery, array $columns = null)
    {
        $this->select = $select;
        $this->joinQuery = $joinQuery;

        if (isset($columns) && \count($columns)) {
            $this->setColumns($columns);
        }
    }

    /**
     * @param     $start
     * @param int $count
     *
     * @return Select
     */
    public function limit($start, $count = 0)
    {
        return $this->select->limit($start, $count);
    }

    /**
     * @param string $whereOperator
     *
     * @return \NilPortugues\Sql\QueryBuilder\Syntax\Where
     */
    public function where($whereOperator = 'AND')
    {
        return $this->select->where($whereOperator);
    }

    /**
     * @param string $column
     * @param string $direction
     * @param null   $table
     *
     * @return Select
     */
    public function orderBy($column, $direction = OrderBy::ASC, $table = null)
    {
        return $this->select->orderBy($column, $direction, $table);
    }

    /**
     * @param string[] $columns
     *
     * @return Select
     */
    public function groupBy(array $columns)
    {
        return $this->select->groupBy($columns);
    }

    /**
     * Remueve del arreglo una columna
     *
     * @param string $key
     * @return $this
     */
    public function removeFromGroupBy(string $key)
    {
        return $this->select->removeFromGroupBy($key);
    }

    /**
     * Allows setting a Select query as a column value.
     *
     * @param array $column
     * 
     * @deprecated
     *
     * @return $this
     */
    public function setSelectAsColumn(array $column)
    {
        throw new QueryException("No se ha creado la refactorización");
    }

    /**
     * Allows setting a value to the select statement.
     *
     * @param string $value
     * @param string $alias
     * 
     * @deprecated use addColumnValue
     *
     * @return $this
     */
    public function setValueAsColumn($value, $alias)
    {
        $this->addColumnValue($alias, $value);
        return $this->select;
    }


    /**
     * Allows calculation on columns using predefined SQL functions.
     * 
     * @deprecated usar addColumnFunction
     *
     * @param string   $funcName
     * @param string[] $arguments
     * @param string   $alias
     *
     * @return $this
     */
    public function setFunctionAsColumn($funcName, array $arguments, $alias): Select
    {
        $this->addColumnFunction($alias, $funcName, $arguments);
        return $this->select;
    }


    /**
     * @param string $columnName
     * @param string $alias
     *
     * @return $this
     */
    public function count($columnName = '*', $alias = 'counter')
    {
        $this->columns = $this->addColumnFunction($alias, 'COUNT', [$columnName]);
        $this->isCount = true;

        return $this->select;
    }

    /**
     * @return bool
     */
    public function isCount()
    {
        return $this->isCount;
    }

    /**
     * @return array
     */
    public function getAllColumns()
    {
        return $this->columns;
    }

    /**
     * @return \NilPortugues\Sql\QueryBuilder\Syntax\Column
     *
     * @throws QueryException
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Sets the column names used to write the SELECT statement.
     * If key is set, key is the column's alias. Value is always the column names.
     * 
     * las columnas añadidas con este metodo pasaran a ser columnas comunes
     *
     * @param array $columns
     *
     * @deprecated
     * @return $this
     */
    public function setColumns(array $columns)
    {
        $this->removeDefinedColumns();
        $this->addColumns($columns);
        return $this->select;
    }

    public function removeDefinedColumns()
    {
        $this->columns = [];
        $this->isCount = false;
        $this->select_all = false;
        return $this->select;
    }

    private function pushToColumn($column)
    {
        array_push($this->columns, $column);
        return $this->select;
    }

    public function selectAll(string $table_alias = "")
    {
        if ($table_alias) {
            if ($this->select_all) {
                throw new QueryException("Ya has incluido todas las columnas");
            }
            $this->pushToColumn(new ColumnAll($table_alias));
        } else {
            $this->select_all = true;
            $this->pushToColumn(new ColumnAll());
        }
        return $this->select;
    }

    public function addColumn(string $column, string $alias = "")
    {
        return $this->pushToColumn(new ColumnStandard($column, $alias));
    }

    public function addColumnFunction(string $alias, string $function, array $arguments)
    {
        return $this->pushToColumn(new ColumnFunction($function, $alias, $arguments));
    }

    public function addColumnValue(string $alias, string $value)
    {
        return $this->pushToColumn(new ColumnValue($value, $alias));
    }

    public function addColumnCustom(string $alias, string $column)
    {
        return $this->pushToColumn(new ColumnCustom($alias, $column));
    }

    /**
     * Todas las funciones añadidas en este metodo seran tomadas como columnas comunes
     * @deprecated
     */
    public function addColumns(array $columns)
    {
        foreach ($columns as $key => $value) {
            if (is_int($key)) {
                $this->addColumn($value);
            } else {
                $this->addColumn($value, $key);
            }
        }
        return $this->select;
    }

    public function removeColumn($search)
    {
        $position = 0;
        foreach ($this->columns as $column) {
            $alias = $column->getAlias();
            $list = [$alias];

            if ($column instanceof ColumnStandard) {
                array_push($list, $column->getColumn());
            }

            if (in_array($search, $list)) {
                array_splice($this->columns, $position, 1);
            }

            $position++;
        }
        return $this->select;
    }
}
