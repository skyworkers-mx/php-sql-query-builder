<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 9/12/14
 * Time: 7:11 PM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Sql\QueryBuilder\Manipulation;

/**
 * Class Union.
 */
class Union extends AbstractSetQuery
{
    const UNION = 'UNION';

    public $builder = null;

    public function __construct($builder) {
        $this->builder = $builder;
    }

    /**
     * @return string
     */
    public function partName()
    {
        return 'UNION';
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
            return $this->builder->write($this);
        } catch (\Exception $e) {
            return \sprintf('[%s] %s', \get_class($e), $e->getMessage());
        }
    }
}
