<?php
/**
 * Author: Nil Portugués Calderó <contact@nilportugues.com>
 * Date: 6/12/14
 * Time: 1:28 AM.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NilPortugues\Sql\QueryBuilder\Builder\Syntax;

use NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder;
use NilPortugues\Sql\QueryBuilder\Manipulation\Select;
use NilPortugues\Sql\QueryBuilder\Syntax\Column;
use NilPortugues\Sql\QueryBuilder\Syntax\SyntaxFactory;

/**
 * Class ColumnWriter.
 */
class ColumnWriter
{
    /**
     * @var \NilPortugues\Sql\QueryBuilder\Builder\GenericBuilder
     */
    protected $writer;

    /**
     * @var PlaceholderWriter
     */
    protected $placeholderWriter;

    /**
     * @param GenericBuilder    $writer
     * @param PlaceholderWriter $placeholderWriter
     */
    public function __construct(GenericBuilder $writer, PlaceholderWriter $placeholderWriter)
    {
        $this->writer = $writer;
        $this->placeholderWriter = $placeholderWriter;
    }

    /**
     * @param Select $select
     *
     * @return array
     */
    public function writeSelectsAsColumns(Select $select)
    {
        $selectAsColumns = $select->getColumnSelects();

        if (!empty($selectAsColumns)) {
            $selectWriter = WriterFactory::createSelectWriter($this->writer, $this->placeholderWriter);
            $selectAsColumns = $this->selectColumnToQuery($selectAsColumns, $selectWriter);
        }

        return $selectAsColumns;
    }

    /**
     * @param array        $selectAsColumns
     * @param SelectWriter $selectWriter
     *
     * @return mixed
     */
    protected function selectColumnToQuery(array &$selectAsColumns, SelectWriter $selectWriter)
    {
        \array_walk(
            $selectAsColumns,
            function (&$column) use (&$selectWriter) {
                $keys = \array_keys($column);
                $key = \array_pop($keys);

                $values = \array_values($column);
                $value = $values[0];

                if (\is_numeric($key)) {
                    /* @var Column $value */
                    $key = $this->writer->writeTableName($value->getTable());
                }
                $column = $selectWriter->selectToColumn($key, $value);
            }
        );

        return $selectAsColumns;
    }

    /**
     * @param Select $select
     *
     * @return array
     */
    public function writeValueAsColumns(Select $select)
    {
        $valueAsColumns = $select->getColumnValues();
        $newColumns = [];

        if (!empty($valueAsColumns)) {
            foreach ($valueAsColumns as $alias => $value) {
                // $value = $this->writer->writePlaceholderValue($value);
                $newValueColumn = array($alias => $value);
                // $newColumns[] = $newValueColumn;
                $column = SyntaxFactory::createColumn($newValueColumn, null);
                $column->setIfIsAlias(true);
                $newColumns[] = $column;
            }
        }

        return $newColumns;
    }

    /**
     * @param Select $select
     *
     * @return array
     */
    public function writeFuncAsColumns(Select $select)
    {
        $funcAsColumns = $select->getColumnFuncs();
        $newColumns = [];

        if (!empty($funcAsColumns)) {
            foreach ($funcAsColumns as $alias => $value) {
                $funcName = $value['func'];
                $funcArgs = (!empty($value['args'])) ? '('.implode(', ', $value['args']).')' : '';

                $newFuncColumn = array($alias => $funcName.$funcArgs);
                $newColumns[] = SyntaxFactory::createColumn($newFuncColumn, null);
            }
        }

        return $newColumns;
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    public function writeColumnWithAlias(Column $column)
    {
        if (($alias = $column->getAlias()) && !$column->isAll()) {
            return $this->writeColumn($column).' AS '.$this->writer->writeColumnAlias($alias);
        }

        return $this->writeColumn($column);
    }

    /**
     * @param Column $column
     *
     * @return string
     */
    public function writeColumn(Column $column)
    {
        # TODO: 🚀 ⚠️
        # TRAZAR CON MAS CALMA COMO FUNCIONA
        # PARA ELIMINAR TODO ESTE BLOQUE DE CODIGO PROBABLEMENTE INNECESARIO
        $name = $this->writer->writeColumnName($column);
        if ($name === Column::ALL) {
            return $this->writer->writeColumnAll();
        }
        $function = substr($name, 0, strpos($name, '('));
        $name = str_replace($function . '(', "", $name);
        $name = str_replace(')', "", $name);
        $name = str_replace("`", "", $name);
        $name = explode(".", $name);
        $is_alias = $column->isAlias();
        if($is_alias) {
            array_walk($name, function (&$column) {
                $column = "'{$column}'";
            });
        } else {
            array_walk($name, function (&$column) {
                $column = "`{$column}`";
            });
        }
        $name = implode(".", $name);
        if($function) {
            return "{$function}({$name})";
        }
        return $name;
    }
}
