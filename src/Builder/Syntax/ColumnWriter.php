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
        $column_name = $this->writer->writeColumnName($column);
        # Verifica si la columna es un *
        if ($column_name === Column::ALL) {
            return $this->writer->writeColumnAll();
        }
        # Este regex ayuda a identificar si hay alguna funcion en la columna
        # asegurate de que si cambias el nombre del grupo de name a otra cosa
        # se cambie en donde corresponda
        $function_regex = '/(?<name>\w+)\s*\(\s*/';
        # Se verifica de esta forma de primera instancia ya que 
        # si se usa preg_match_all y despues un count
        # al contar contaria matches vacios o.O
        $function_exist = \preg_match($function_regex, $column_name);
        # Simple inicialización
        $function_name = "";
    
        if($function_exist) {
            $function_matches = [];
            \preg_match_all($function_regex, $column_name, $function_matches);
            $function_count = \count($function_matches);
            # Verificando si se utilizo mas de una funcion
            # dado que $column_name contiene el valor correspondiente
            # se envia por asi decirlo el texto en RAW
            if($function_count > 1) {
                return $column_name;
            # En este caso solo se estaria usando una funcion
            # se remplaza los parentesis para despues 
            } else if($function_count == 1) {
                $function_name = $function_matches["name"][0];
                $column_name = \str_replace($function_name, "", $column_name);
                $column_name = \ltrim($column_name, "(");
                $column_name = \rtrim($column_name, ")");
            }
        }
        # FIX alias erroneos...
        # se remplazan todas las comills inglesas
        $column_name = \str_replace("`", "", $column_name);
        # se delimita codigo
        $column_name = explode(".", $column_name);
        # Se verifica si es una columna de alias
        $is_alias = $column->isAlias();
        if($is_alias) {
            \array_walk($column_name, function (&$column) {
                $column = "'{$column}'";
            });
        } else {
            \array_walk($column_name, function (&$column) {
                if (!is_numeric($column)){
                    $column = "`{$column}`";
                }
            });
        }
        # Convirtiendo arreglo a string
        $column_name = \implode(".", $column_name);
        # Verificando si hay una funcion
        if($function_exist) {
            return "{$function_name}({$column_name})";
        }
        return $column_name;
    }
}
