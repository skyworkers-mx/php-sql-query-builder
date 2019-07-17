<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 12/24/14
 * Time: 12:30 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Sql\QueryBuilder\Manipulation;

/**
 * Class AbstractSetQuery.
 */
abstract class UnionSetQuery extends AbstractSetQuery
{
    public function __construct($builder)
    {
        $this->builder = $builder;
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
    public function getAllOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Converts this query into an SQL string by using the injected builder.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->builder->writeFormattedWithValues($this);
        } catch (\Exception $e) {
            return \sprintf('[%s] %s', \get_class($e), $e->getMessage());
        }
    }
}
