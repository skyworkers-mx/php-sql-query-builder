<?php

use PHPUnit\Framework\TestCase;
use NilPortugues\Sql\QueryBuilder\Builder\MySqlBuilder;

/**
 * Class MySQL ./bin/phpunit --bootstrap vendor/autoload.php tests/MySQL
 */
class MySQL extends TestCase
{
	/**
	 * @test
	 */
	public function queryUnion()
	{

		$builder = new MySqlBuilder();
		$union = $builder->unionAll();

		$q1 = $builder->select();
		$q1->setTable(["U" => "users"]);


		$q2 = $builder->select();
		$q2->setTable("users");

		$union->add($q1)->add($q2);
		$union->orderBy("id", "ASC");
		$union->limit(5, 10);

		echo $union;
	}

	/**
	 * @test
	 */
	public function queryDinamyc()
	{

		$builder = new MySqlBuilder();

		$q1 = $builder->select();

		$q1->setTable(["U" => "users"])
			->setValueAsColumn('Static_value', 'VALUE')
			->setFunctionAsColumn('SUM', ['field1, field2'], 'sum_column');

		$q1
			->innerJoin(["A" => "a_test1"], "A.id", "U.test_id")
			->rightJoin(["B" => "b_utest2"], "B.id", "U.test_id");

		$q1
			->leftJoin(["C" => "c_test3"], "C.id", "U.test_id")
			->rightJoin(["D" => "d_test4"], "D.id", "U.test_id")
			->innerJoin(["F" => "f_test5"], "F.id", "U.test_id")
			->on('OR')
			->equals("F.status", 1)
			->end()
			->innerJoin(["G" => "g_test6"], "F.id", "U.test_id");

		$q1->groupBy(["F.id"]);
		$q1->orderBy("F.id", "ASC");

		echo $q1;
	}

	/**
	 * @test
	 */
	public function queryUpdateJoin()
	{
		$builder = new MySqlBuilder();
		$query = $builder->update();
		$query->setTable(["A" => "a_test"])
			->leftJoin(["B" => "b_test"], "B.b_test_id", "A.id")
			->leftJoin(["C" => "c_test"], "C.id", "B.b_test_id")
			->leftJoin(["D" => "d_test"], "D.d_test_id", "A.id")
			->leftJoin(["E" => "e_test"], "E.id", "D.d_test_id")
			->setValues([
				'C.field5' => 2,
				'E.field5' => 2
			])
			->where()->in('A.id', [20, 23, 25, 28])->end();
		echo $query;
	}

	/**
	 * @test
	 */
	public function querySubquery()
	{
		$builder = new MySqlBuilder();
		$q1 = $builder->select();
		$q1->setTable(["A" => "a_test"]);
		$q2 = $builder->select();
		$q2->setTable("users")
			->innerJoin(["B" => $q1], "B.id_test_a", "A.id");
		echo $q2;
	}
}
