<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 9/12/14
 * Time: 7:15 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Sql\QueryBuilder\Builder\Syntax;

use NilPortugues\Sql\QueryBuilder\Manipulation\UnionAll;
use NilPortugues\Sql\QueryBuilder\Syntax\OrderBy;

/**
 * Class UnionAllWriter.
 */
class UnionAllWriter extends AbstractSetWriter
{
    /**
     * @param UnionAll $unionAll
     *
     * @return string
     */
    public function write(UnionAll $unionAll)
    {


        $orderBy = $this->writeSelectOrderBy( $unionAll);
        $command = $this->abstractWrite($unionAll, 'getUnions', UnionAll::UNION_ALL);
        $command .= "" .$orderBy;

        return $command;
    }

    /**
     * @param Select $select
     * @param array  $parts
     *
     * @return $this
     */
    protected function writeSelectOrderBy( UnionAll $select)
    {
        $str = '';
        if (\count($select->getAllOrderBy())) {
            $orderByArray = $select->getAllOrderBy();
            \array_walk(
                $orderByArray,
                function (&$orderBy) {
                    $orderBy = $this->writeOrderBy($orderBy);
                }
            );

            $str = 'ORDER BY ';
            $str .= \implode(', ', $orderByArray);
        }

        return $str;
    }

    /**
     * @param OrderBy $orderBy
     *
     * @return string
     */
    public function writeOrderBy(OrderBy $orderBy)
    {
        $column = $this->columnWriter->writeColumn($orderBy->getColumn());

        return $column . ' ' . $orderBy->getDirection();
    }
}
