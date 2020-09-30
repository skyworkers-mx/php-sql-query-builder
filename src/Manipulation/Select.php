<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 6/3/14
 * Time: 12:07 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Sql\QueryBuilder\Manipulation;

use NilPortugues\Sql\QueryBuilder\Syntax\SyntaxFactory;
use NilPortugues\Sql\QueryBuilder\Syntax\Table;
use NilPortugues\Sql\QueryBuilder\Syntax\Where;
use NilPortugues\Sql\QueryBuilder\Syntax\OrderBy;

/**
 * Class Select.
 */
class Select extends AbstractJoinQuery
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * @var array
     */
    protected $groupBy = [];

    /**
     * @var string
     */
    protected $camelCaseTableName = '';

    /**
     * @var Where
     */
    protected $having;

    /**
     * @var string
     */
    protected $havingOperator = 'AND';

    /**
     * @var bool
     */
    protected $isDistinct = false;

    /**
     * @var Where
     */
    protected $where;


    /**
     * @var ColumnQuery
     */
    protected $columnQuery;


    /**
     * Identifier for query cache
     * @var string
     */
    protected $id = '';

    protected $partitions = [];

    /**
     * @param string $table
     * @param array  $columns
     */
    public function __construct($table = null, array $columns = null)
    {
        parent::__construct();
        if (isset($table)) {
            $this->setTable($table);
        }
        $this->columnQuery = new ColumnQuery($this, $this->joinQuery, $columns);
    }

    public function setTable($table, $partitions = null)
    {
        parent::setTable($table);

        $this->setPartitions($partitions);

        return $this;
    }

    public function selectAll(string $table_alias = ""): Select
    {
        return $this->columnQuery->selectAll($table_alias);
    }

    /**
     * Define an id
     * 
     * this can be used for example when you want to store this with MemCache/MemCached and you need a unique key
     *
     * @param string $id
     * @return void
     */
    public function setId(string $id): Select
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * This __clone method will create an exact clone but without the object references due to the fact these
     * are lost in the process of serialization and un-serialization.
     *
     * @return Select
     */
    public function __clone()
    {
        return \unserialize(\serialize($this));
    }

    /**
     * @return string
     */
    public function partName()
    {
        return 'SELECT';
    }

    /**
     * @return array
     */
    public function getAllColumns()
    {
        return $this->columnQuery->getAllColumns();
    }

    /**
     * @return \NilPortugues\Sql\QueryBuilder\Syntax\Column
     *
     * @throws QueryException
     */
    public function getColumns(): array
    {
        return $this->columnQuery->getColumns();
    }

    /**
     * Sets the column names used to write the SELECT statement.
     * If key is set, key is the column's alias. Value is always the column names.
     *
     * @param string[] $columns
     *
     * @return ColumnQuery
     */
    public function setColumns(array $columns)
    {
        return $this->columnQuery->setColumns($columns);
    }


    public function setPartitions($partitions = null)
    {
        if (is_callable($partitions)) {
            $partitions = $partitions();
        }

        if (is_array($partitions)) {
            $this->partitions = $partitions;
        }

        return $this;
    }

    public function getPartitions(): array
    {
        return $this->partitions;
    }

    /**
     * Añade una columna
     * @param string $column 
     * @param string $alias 
     * @return Select 
     */
    public function addColumn(string $column, string $alias = ""): Select
    {
        return $this->columnQuery->addColumn($column, $alias);
    }

    /**
     * Añade una columna como función 
     * @param string $alias 
     * @param string $function 
     * @param array $arguments 
     * @return Select 
     */
    public function addColumnFunction(string $alias, string $function, array $arguments): Select
    {
        return $this->columnQuery->addColumnFunction($alias, $function, $arguments);
    }

    /**
     * Añade un valor como columna
     * @param string $alias 
     * @param string $value 
     * @return Select 
     */
    public function addColumnValue(string $alias, string $value): Select
    {
        return $this->columnQuery->addColumnValue($alias, $value);
    }

    /**
     * Añade una columna con cualquier valor literal
     * @param string $alias 
     * @param string $column 
     * @return Select 
     */
    public function addColumnCustom(string $alias, string $column): Select
    {
        return $this->columnQuery->addColumnCustom($alias, $column);
    }


    /**
     * @deprecated por favor utiliza addColumn
     * @param mixed $columns 
     * @return Select 
     */
    public function addColumns($columns): Select
    {
        return $this->columnQuery->addColumns($columns);
    }

    public function removeColumn($column)
    {
        return $this->columnQuery->removeColumn($column);
    }

    /**
     * Allows setting a Select query as a column value.
     *
     * @param array $column
     *
     * @return ColumnQuery
     */
    public function setSelectAsColumn(array $column)
    {
        return $this->columnQuery->setSelectAsColumn($column);
    }

    /**
     * Allows setting a value to the select statement.
     *
     * @param string $value
     * @param string $alias
     * 
     * @deprecated
     *
     * @return ColumnQuery
     */
    public function setValueAsColumn($value, $alias)
    {
        return $this->columnQuery->setValueAsColumn($value, $alias);
    }

    /**
     * Allows calculation on columns using predefined SQL functions.
     *
     * @param string   $funcName
     * @param string[] $arguments
     * @param string   $alias
     * 
     * @deprecated
     *
     * @return ColumnQuery
     */
    public function setFunctionAsColumn($funcName, array $arguments, $alias): Select
    {
        return $this->columnQuery->setFunctionAsColumn($funcName, $arguments, $alias);
    }

    /**
     * Returns all the Where conditions to the BuilderInterface class in order to write the SQL WHERE statement.
     *
     * @return array
     */
    public function getAllWheres()
    {
        return $this->getAllOperation($this->where, 'getAllWheres');
    }

    /**
     * @return array
     */
    public function getAllHavings()
    {
        return $this->getAllOperation($this->having, 'getAllHavings');
    }

    /**
     * @param string $columnName
     * @param string $alias
     *
     * @return ColumnQuery
     */
    public function count($columnName = '*', $alias = 'counter')
    {
        return $this->columnQuery->count($columnName, $alias);
    }

    /**
     * @return bool
     */
    public function isCount()
    {
        return $this->columnQuery->isCount();
    }

    /**
     * @param int $start
     * @param     $count
     *
     * @return $this
     */
    public function limit($start, $count = 0)
    {
        $this->limitStart = $start;
        $this->limitCount = $count;

        return $this;
    }

    /**
     * @return array
     */
    public function getGroupBy()
    {
        return SyntaxFactory::createColumns($this->groupBy, $this->getTable());
    }

    /**
     * @param string[] $columns
     *
     * @return $this
     */
    public function groupBy(array $columns)
    {
        $this->groupBy = $columns;

        return $this;
    }

    /**
     * Remueve del arreglo una columna
     *
     * @param string $key
     * @return $this
     */
    public function removeFromGroupBy(string $key)
    {
        $key = array_search($key, $this->groupBy);
        \array_splice($this->groupBy, $key, 1);
        return $this;
    }

    /**
     * @param $havingOperator
     *
     * @throws QueryException
     *
     * @return Where
     */
    public function having($havingOperator = 'AND')
    {
        if (!isset($this->having)) {
            $this->having = QueryFactory::createWhere($this);
        }

        if (!in_array($havingOperator, array(Where::CONJUNCTION_AND, Where::CONJUNCTION_OR))) {
            throw new QueryException(
                "Invalid conjunction specified, must be one of AND or OR, but '" . $havingOperator . "' was found."
            );
        }

        $this->havingOperator = $havingOperator;

        return $this->having;
    }

    /**
     * @return string
     */
    public function getHavingOperator()
    {
        return $this->havingOperator;
    }

    /**
     * @return $this
     */
    public function distinct()
    {
        $this->isDistinct = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDistinct()
    {
        return $this->isDistinct;
    }

    /**
     * @return array
     */
    public function getAllOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @return ParentQuery
     */
    public function getParentQuery()
    {
        return $this->parentQuery;
    }

    /**
     * @param string $column
     * @param string $direction
     * @param null   $table
     *
     * @return $this
     */
    public function orderBy($column, $direction = OrderBy::ASC, $table = null)
    {
        $current = parent::orderBy($column, $direction, $table);
        if ($this->getParentQuery() != null) {
            $this->getParentQuery()->orderBy($column, $direction, \is_null($table) ? $this->getTable() : $table);
        }
        return $current;
    }
}
