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

use NilPortugues\Sql\QueryBuilder\Syntax\Where;

/**
 * Class Update.
 */
class AbstractJoinQuery extends AbstractBaseQuery
{
    /**
     * @var JoinQuery
     */
    protected $joinQuery;

    /**
     * @var ParentQuery
     */
    protected $parentQuery;

    /**
     * @param string $table
     * @param array  $columns
     */
    public function __construct()
    {
        $this->joinQuery = new JoinQuery($this);
    }

    /**
     * @return string
     */
    public function partName()
    {
        return '';
    }

    /**
     * @param string   $table
     * @param string   $selfColumn
     * @param string   $refColumn
     * @param string[] $columns
     *
     * @return Select
     */
    public function leftJoin($table, $selfColumn = null, $refColumn = null, $partitions = null)
    {
        return $this->joinQuery->leftJoin($table, $selfColumn, $refColumn, $partitions);
    }

    /**
     * @param string   $table
     * @param string   $selfColumn
     * @param string   $refColumn
     * @param string[] $columns
     *
     * @internal param null $selectClass
     *
     * @return Select
     */
    public function rightJoin($table, $selfColumn = null, $refColumn = null, $partitions = null)
    {
        return $this->joinQuery->rightJoin($table, $selfColumn, $refColumn, $partitions);
    }

    /**
     * @param string   $table
     * @param string   $selfColumn
     * @param string   $refColumn
     * @param string[] $columns
     *
     * @return Select
     */
    public function innerJoin($table, $selfColumn = null, $refColumn = null, $partitions = null)
    {
        return $this->joinQuery->innerJoin($table, $selfColumn, $refColumn, $partitions);
    }

    /**
     * Alias to joinCondition.
     *
     * @return Where
     */
    public function on(string $conjunction = '')
    {
        return $this->joinQuery->on($conjunction);
    }

    /**
     * @param Select $parentQuery
     *
     * @return $this
     */
    public function setParentQuery($parentQuery)
    {
        $this->parentQuery = $parentQuery;

        return $this;
    }

    /**
     * @return array
     */
    public function getAllJoins()
    {
        return $this->joinQuery->getAllJoins();
    }

    /**
     * @param string   $table
     * @param string   $selfColumn
     * @param string   $refColumn
     * @param string[] $columns
     * @param string   $joinType
     *
     * @return Select
     */
    public function join(
        $table,
        $selfColumn = null,
        $refColumn = null,
        $columns = [],
        $joinType = null
    ) {
        return $this->joinQuery->join($table, $selfColumn, $refColumn, $columns, $joinType);
    }

    /**
     * WHERE constrains used for the ON clause of a (LEFT/RIGHT/INNER/CROSS) JOIN.
     *
     * @return Where
     */
    public function joinCondition()
    {
        return $this->joinQuery->joinCondition();
    }

    /**
     * @param Select $select
     * @param string $selfColumn
     * @param string $refColumn
     *
     * @return Select
     */
    public function addJoin(AbstractJoinQuery $select, $selfColumn, $refColumn)
    {
        return $this->joinQuery->addJoin($select, $selfColumn, $refColumn);
    }

    /**
     * Transforms Select in a joint.
     *
     * @param bool $isJoin
     *
     * @return JoinQuery
     */
    public function isJoin($isJoin = true)
    {
        return $this->joinQuery->setJoin($isJoin);
    }

    /**
     * @param string   $table
     * @param string   $selfColumn
     * @param string   $refColumn
     * @param string[] $columns
     *
     * @return Select
     */
    public function crossJoin($table, $selfColumn = null, $refColumn = null, $partitions = null)
    {
        return $this->joinQuery->crossJoin($table, $selfColumn, $refColumn, $partitions);
    }

    /**
     * @return bool
     */
    public function isJoinSelect()
    {
        return $this->joinQuery->isJoin();
    }

    /**
     * @param null|Where $data
     * @param string     $operation
     *
     * @return array
     */
    protected function getAllOperation($data, $operation)
    {
        $collection = [];

        if (!is_null($data)) {
            $collection[] = $data;
        }

        foreach ($this->joinQuery->getJoins() as $join) {
            $collection = \array_merge($collection, $join->$operation());
        }

        return $collection;
    }

    /**
     * @return Where
     */
    public function getJoinCondition(): Where
    {
        return $this->joinQuery->getJoinCondition();
    }

    /**
     * @return string
     */
    public function getJoinType()
    {
        return $this->joinQuery->getJoinType();
    }

    /**
     * @param string|null $joinType
     *
     * @return $this
     */
    public function setJoinType($joinType)
    {
        $this->joinQuery->setJoinType($joinType);

        return $this;
    }
}
