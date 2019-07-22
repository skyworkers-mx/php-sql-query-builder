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
		
		$q1->setTable(["U" => "users"]);

		$q1
			->innerJoin(["A" => "a_test1"], "A.id", "U.test_id")
			->rightJoin(["B" => "b_utest2"], "B.id", "U.test_id")
		
		;
		
		$q1
			->leftJoin(["C" => "c_test3"], "C.id", "U.test_id")
			->rightJoin(["D" => "d_test4"], "D.id", "U.test_id")
			->innerJoin(["F" => "f_test5"], "F.id", "U.test_id")
				->on()
					->equals("F.status", 1)
				->end()
			->innerJoin(["G" => "g_test6"], "F.id", "U.test_id")
		;

		$q1->groupBy(["F.id"]);

		echo $q1;
	}
}
