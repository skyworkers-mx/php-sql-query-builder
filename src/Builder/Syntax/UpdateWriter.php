<?php

/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 6/11/14
 * Time: 1:51 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Sql\QueryBuilder\Builder\Syntax;

use NilPortugues\Sql\QueryBuilder\Manipulation\QueryException;
use NilPortugues\Sql\QueryBuilder\Manipulation\Update;
use NilPortugues\Sql\QueryBuilder\Syntax\SyntaxFactory;

/**
 * Class UpdateWriter.
 */
class UpdateWriter extends AbstractBaseWriter
{
    /**
     * @param Update $update
     *
     * @throws QueryException
     *
     * @return string
     */
    public function write(Update $update)
    {
        $values = $update->getValues();
        if (empty($values)) {
            throw new QueryException('No values to update in Update query.');
        }

        $parts = array(
            'UPDATE ' .
                $this->writer->writeTableWithAlias($update->getTable()) .
                $this->writeSelectAggrupation($update, $this->writer, 'getAllJoins', 'writeJoin', ' ') . ' SET ',
            $this->writeUpdateValues($update),
        );

        AbstractBaseWriter::writeWhereCondition($update, $this->writer, $this->placeholderWriter, $parts);
        AbstractBaseWriter::writeLimitCondition($update, $this->placeholderWriter, $parts);
        $comment = AbstractBaseWriter::writeQueryComment($update);

        return $comment . implode(' ', $parts);
    }

    /**
     * @param Select $select
     * @param        $writer
     * @param string $getMethod
     * @param string $writeMethod
     * @param string $glue
     * @param string $prepend
     *
     * @return string
     */
    protected function writeSelectAggrupation($select, $writer, $getMethod, $writeMethod, $glue, $prepend = '')
    {
        $str = '';
        $joins = $select->$getMethod();

        if (!empty($joins)) {
            \array_walk(
                $joins,
                function (&$join) use ($writer, $writeMethod) {
                    $join = $writer->$writeMethod($join);
                }
            );

            $str = $prepend . implode($glue, $joins);
        }

        return $str;
    }

    /**
     * @param Update $update
     *
     * @return string
     */
    protected function writeUpdateValues(Update $update)
    {
        $assigns = [];
        foreach ($update->getValues() as $column => $value) {
            $newColumn = array($column);
            $column = $this->columnWriter->writeColumn(SyntaxFactory::createColumn($newColumn, $update->getTable()));

            $value = $this->writer->writePlaceholderValue($value);

            $assigns[] = "$column = $value";
        }

        return \implode(', ', $assigns);
    }
}
