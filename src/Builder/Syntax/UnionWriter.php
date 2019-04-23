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

use NilPortugues\Sql\QueryBuilder\Manipulation\Union;
use NilPortugues\Sql\QueryBuilder\Syntax\OrderBy;

/**
 * Class UnionWriter.
 */
class UnionWriter extends AbstractSetWriter
{
    /**
     * @param Union $union
     *
     * @return string
     */
    public function write(Union $union)
    {
        
        $orderBy = $this->writeSelectOrderBy($union);
        $command = $this->abstractWrite($union, 'getUnions', Union::UNION);
        $command .= $orderBy;
        
        // $command .= $orderBy;
        return $command;
    }

    /**
     * @param Select $select
     * @param array  $parts
     *
     * @return $this
     */
    protected function writeSelectOrderBy(Union $select)
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
