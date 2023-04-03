<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/25/14
 * Time: 11:41 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Sql\QueryBuilder\Manipulation;

use NilPortugues\Sql\QueryBuilder\Syntax\Where;
use NilPortugues\Sql\QueryBuilder\Syntax\Column;
use NilPortugues\Sql\QueryBuilder\Syntax\SyntaxFactory;

use NilPortugues\Sql\QueryBuilder\Manipulation\Select;

/**
 * Class JoinQuery.
 */
class JoinQuery
{
    const JOIN_LEFT = 'LEFT';
    const JOIN_RIGHT = 'RIGHT';
    const JOIN_INNER = 'INNER';
    const JOIN_CROSS = 'CROSS';

    /**
     * @var Where
     */
    protected $joinCondition;

    /**
     * @var bool
     */
    protected $isJoin = false;

    /**
     * Se guarda el ultimo join añadido,
     * dado que regresar el $select da problemas al utilizar on()
     *
     * @var Select
     */
    protected $last_join = null;

    /**
     * @var string
     */
    protected $joinType;

    /**
     * @var array
     */
    protected $joins = [];

    /**
     * @var Select
     */
    protected $select;

    /**
     * @param Select $select
     */
    public function __construct(AbstractJoinQuery $select)
    {
        $this->select = $select;
    }

    /**
     * @param string $table
     *
     * @return $this
     */
    public function setTable($table)
    {
        $this->select->setTable($table);

        return $this;
    }

    /**
     * @param string   $table
     * @param mixed    $selfColumn
     * @param mixed    $refColumn
     * @param string[] $columns
     *
     * @return Select
     */
    public function leftJoin($table, $selfColumn = null, $refColumn = null, $partitions = null)
    {
        return $this->join($table, $selfColumn, $refColumn, $partitions, self::JOIN_LEFT);
    }

    /**
     * @param string   $table
     * @param mixed    $selfColumn
     * @param mixed    $refColumn
     * @param string[] $columns
     * @param string   $joinType
     *
     * @return Select
     */
    public function join(
        $table,
        $selfColumn = null,
        $refColumn = null,
        $partitions = null,
        $joinType = null
    ) {
        $table = gettype($table) == "string" ? [$table] : $table;
        $keys = array_keys($table);
        $instance = $table[$keys[0]];

        if ($instance instanceof AbstractJoinQuery) {
            $table_name = $instance->getTable()->getAlias();
        } else {
            $table_name = $keys[0] . "_" . $instance;
        }

        if (!isset($this->joins[$table_name])) {
            $select = QueryFactory::createSelect($table);
            $select->setColumns([]);
            $select->setPartitions($partitions);
            $select->setJoinType($joinType);
            $select->setParentQuery($this->select);
            $this->addJoin($select, $selfColumn, $refColumn);
        }

        return $this->select;
    }

    /**
     * @param Select $select
     * @param mixed  $selfColumn
     * @param mixed  $refColumn
     *
     * @return Select
     */
    public function addJoin(AbstractJoinQuery $select, $selfColumn, $refColumn)
    {
        $select->isJoin(true);
        $instance = $select->getTable()->getName();

        if ($instance instanceof AbstractJoinQuery) {
            $table_name = $instance->getTable()->getAlias();
        } else {
            $table_name = $select->getTable()->getAlias() . "_" . $instance;
        }

        if (!isset($this->joins[$table_name])) {
            if (!$selfColumn instanceof Column) {
                $newColumn = array($selfColumn);
                $selfColumn = SyntaxFactory::createColumn(
                    $newColumn,
                    $this->select->getTable()
                );
            }

            if ($selfColumn && $refColumn) {
                $select->joinCondition()->equals($refColumn, $selfColumn);
            }

            $this->joins[$table_name] = $select;
        }

        $this->last_join = $this->joins[$table_name];

        return $this->select;
    }

    /**
     * Transforms Select in a joint.
     *
     * @param bool $isJoin
     *
     * @return $this
     */
    public function setJoin($isJoin = true)
    {
        $this->isJoin = $isJoin;

        return $this;
    }

    /**
     * @param string   $table
     * @param mixed    $selfColumn
     * @param mixed    $refColumn
     * @param string[] $columns
     *
     * @internal param null $selectClass
     *
     * @return Select
     */
    public function rightJoin($table, $selfColumn = null, $refColumn = null, $partitions = null)
    {
        return $this->join($table, $selfColumn, $refColumn, $partitions, self::JOIN_RIGHT);
    }

    /**
     * @param string   $table
     * @param mixed    $selfColumn
     * @param mixed    $refColumn
     * @param string[] $columns
     *
     * @return Select
     */
    public function crossJoin($table, $selfColumn = null, $refColumn = null, $partitions = null)
    {
        return $this->join($table, $selfColumn, $refColumn, $partitions, self::JOIN_CROSS);
    }

    /**
     * @param string   $table
     * @param mixed    $selfColumn
     * @param mixed    $refColumn
     * @param string[] $columns
     *
     * @return Select
     */
    public function innerJoin($table, $selfColumn = null, $refColumn = null, $partitions = null)
    {
        return $this->join($table, $selfColumn, $refColumn, $partitions, self::JOIN_INNER);
    }

    /**
     * Alias to joinCondition.
     *
     * @return Where
     */
    public function on(string $conjunction = '')
    {
        if (is_object($this->last_join) && method_exists($this->last_join, "joinCondition")) {
            $where_instancie = $this->last_join->joinCondition();
        } else {
            $where_instancie = $this->joinCondition();
        }
        if (strtoupper($conjunction) == Where::CONJUNCTION_OR) {
            $where_instancie->conjunction(Where::CONJUNCTION_OR);
        }
        return $where_instancie;
    }

    /**
     * WHERE constrains used for the ON clause of a (LEFT/RIGHT/INNER/CROSS) JOIN.
     *
     * @return Where
     */
    public function joinCondition()
    {
        if (!isset($this->joinCondition)) {
            $this->joinCondition = QueryFactory::createWhere($this->select);
        }

        return $this->joinCondition;
    }

    /**
     * @return bool
     */
    public function isJoinSelect()
    {
        return $this->isJoin;
    }

    /**
     * @return bool
     */
    public function isJoin()
    {
        return $this->isJoin;
    }

    /**
     * @return \NilPortugues\Sql\QueryBuilder\Syntax\Where
     */
    public function getJoinCondition()
    {
        return $this->joinCondition;
    }

    /**
     * @param \NilPortugues\Sql\QueryBuilder\Syntax\Where $joinCondition
     *
     * @return $this
     */
    public function setJoinCondition($joinCondition)
    {
        $this->joinCondition = $joinCondition;

        return $this;
    }

    /**
     * @return string
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * @param string $joinType
     *
     * @return $this
     */
    public function setJoinType($joinType)
    {
        $this->joinType = $joinType;

        return $this;
    }

    /**
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }

    /**
     * @param array $joins
     *
     * @return $this
     */
    public function setJoins($joins)
    {
        $this->joins = $joins;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllJoins()
    {
        $joins = $this->joins;

        foreach ($this->joins as $join) {
            $joins = \array_merge($joins, $join->getAllJoins());
        }

        return $joins;
    }
}
