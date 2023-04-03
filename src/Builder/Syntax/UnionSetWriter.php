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

use NilPortugues\Sql\QueryBuilder\Manipulation\UnionSetQuery as Union;
use NilPortugues\Sql\QueryBuilder\Syntax\OrderBy;
use PHPUnit\Runner\Exception;

/**
 * Class UnionAllWriter.
 */
class UnionSetWriter extends AbstractSetWriter
{
	public $union_type = "";
	/**
	 * @param UnionAll $union
	 *
	 * @return string
	 */
	public function write(Union $union)
	{
		if(!$this->union_type) {
			throw new Exception("Por favor define el tipo de union del query");
		}
		$parts = [];
		$this->writeSelectOrderBy($union, $parts);
		$this->writeSelectLimit($union, $parts);
		return $this->abstractWrite($union, 'getUnions', $this->union_type).implode("\n", $parts);
	}

	/**
	 * @param Union $union
	 * @param array  $parts
	 *
	 * @return $this
	 */
	protected function writeSelectOrderBy(Union $union, array &$parts)
	{
		$str = '';
		if (\count($union->getAllOrderBy())) {
			$orderByArray = $union->getAllOrderBy();
			\array_walk(
				$orderByArray,
				function (&$orderBy) {
					$orderBy = $this->writeOrderBy($orderBy);
				}
			);

			$str = 'ORDER BY ';
			$str .= \implode(', ', $orderByArray);
		}

		array_push($parts, $str);
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

	/**
	 * @param Union $union
	 *
	 * @return string
	 */
	protected function getStartingLimit(Union $union)
	{
		return (null === $union->getLimitStart() || 0 == $union->getLimitStart()) ? '0' : '1';
	}

	/**
	 * @param Union $union
	 *
	 * @return string
	 */
	protected function getLimitCount(Union $union)
	{
		return (null === $union->getLimitCount()) ? '0' : '1';
	}

	/**
	 * @param Union $union
	 * @param array  $parts
	 *
	 * @return $this
	 */
	protected function writeSelectLimit(Union $union, array &$parts)
	{
		$mask = $this->getStartingLimit($union) . $this->getLimitCount($union);

		$limit = '';

		if ($mask !== '00') {
			$start = $this->placeholderWriter->add($union->getLimitStart());
			$count = $this->placeholderWriter->add($union->getLimitCount());

			$limit = "LIMIT {$start}, {$count}";
		}

		array_push($parts, $limit);
	}
}
