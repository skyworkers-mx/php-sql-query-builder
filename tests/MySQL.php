<?php

use PHPUnit\Framework\TestCase;
use NilPortugues\Sql\QueryBuilder\Builder\MySqlBuilder;

/**
 * Class MySQL ./bin/phpunit --bootstrap vendor/autoload.php tests/MySQL
 */
class MySQL extends TestCase
{

  public function queryToOneLine($query): string
  {
    return trim(preg_replace('/\s+/', ' ', (string)$query));
  }

  public function arrayTokeys($array)
  {
    return implode(", ", array_map(fn ($value) => "`$value`", array_keys($array)));
  }

  public function arrayToValues($array)
  {
    return implode(", ", array_map(function ($value) {
      if (gettype($value) == "string") {
        return "'$value'";
      }
      return $value;
    }, $array));
  }


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

    $q1->limit(10);
    $q2->limit(10);

    $union->add($q1)->add($q2);
    $union->orderBy("id", "ASC");
    $union->limit(5, 10);

    $this->assertEquals(
      $this->queryToOneLine($union),
      "( SELECT * FROM `users` AS `U` LIMIT 10, 0 ) UNION ALL ( SELECT * FROM `users` LIMIT 10, 0 ) ORDER BY `id` ASC LIMIT 5, 10"
    );
  }

  /**
   * @test
   */
  public function queryDinamyc()
  {

    $builder = new MySqlBuilder();

    $q1 = $builder->select();

    $q1
      ->setTable(["U" => "users"])
      ->selectAll()
      ->addColumnValue('alias_custom_value', 1)
      ->addColumnFunction('sum_column', 'SUM', ['field1, field2']);

    $q1
      ->innerJoin(["A" => "a_test1"], "A.id", "U.test_id")
      ->rightJoin(["B" => "b_utest2"], "B.id", "U.test_id");

    $q1
      ->leftJoin(["C" => "c_test3"], "C.id", "U.test_id")
      ->rightJoin(["D" => "d_test4"], "D.id", "U.test_id")
      ->innerJoin(["F" => "f_test5"], "F.id", "U.test_id")
      ->on('OR')
      ->equals("F.status", 1)
      ->end();
    $q1
      ->innerJoin(["G" => "g_test6"], "F.id", "U.test_id");

    $q1->groupBy(["F.id"]);
    $q1->orderBy("F.id", "ASC");

    $this->assertEquals(
      $this->queryToOneLine($q1),
      $this->queryToOneLine(
        "SELECT
              *,
              '1' AS `alias_custom_value`,
              SUM(field1, field2) AS `sum_column`
          FROM
              `users` AS `U`
              INNER JOIN `a_test1` AS `A` ON (`U`.`test_id` = `A`.`id`)
              RIGHT JOIN `b_utest2` AS `B` ON (`U`.`test_id` = `B`.`id`)
              LEFT JOIN `c_test3` AS `C` ON (`U`.`test_id` = `C`.`id`)
              RIGHT JOIN `d_test4` AS `D` ON (`U`.`test_id` = `D`.`id`)
              INNER JOIN `f_test5` AS `F` ON (`U`.`test_id` = `F`.`id`) OR (`F`.`status` = 1)
              INNER JOIN `g_test6` AS `G` ON (`U`.`test_id` = `F`.`id`)
          GROUP BY
              `F`.`id`
          ORDER BY
              `F`.`id` ASC"
      )
    );
  }

  /**
   * @test
   */
  public function queryUpdateJoin()
  {
    $builder = new MySqlBuilder();
    $query = $builder->update();
    $query->setTable(["A" => "a_test"]);
    $query
      ->leftJoin(["B" => "b_test"], "B.b_test_id", "A.id")
      ->leftJoin(["C" => "c_test"], "C.id", "B.b_test_id")
      ->leftJoin(["D" => "d_test"], "D.d_test_id", "A.id")
      ->leftJoin(["E" => "e_test"], "E.id", "D.d_test_id");
    $query
      ->setValues([
        'C.field5' => 2,
        'E.field5' => 2
      ])
      ->where()->in('A.id', [20, 23, 25, 28])->end();

    $this->assertEquals(
      $this->queryToOneLine($query),
      $this->queryToOneLine(
        "UPDATE
            `a_test` AS `A`
            LEFT JOIN `b_test` AS `B` ON (`A`.`id` = `B`.`b_test_id`)
            LEFT JOIN `c_test` AS `C` ON (`B`.`b_test_id` = `C`.`id`)
            LEFT JOIN `d_test` AS `D` ON (`A`.`id` = `D`.`d_test_id`)
            LEFT JOIN `e_test` AS `E` ON (`D`.`d_test_id` = `E`.`id`)
        SET `C`.`field5` = 2, `E`.`field5` = 2
        WHERE ( `A`.`id` IN (20, 23, 25, 28) )"
      )
    );
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

    $this->assertEquals(
      $this->queryToOneLine($q2),
      "SELECT * FROM `users` INNER JOIN ( SELECT * FROM `a_test` AS `A` ) AS `B` ON (`A`.`id` = `B`.`id_test_a`)"
    );
  }

  /**
   * @test
   */
  public function queryInsert()
  {
    $builder = new MySqlBuilder();

    $table = 'CARS';

    $data = [
      'brand' => 'Mitsubishi',
      'model' => 'Lancer',
      'year' => 2012,
      'version' => 'DE',
      'engine' => '2.0',
      'transmission' => 'manual',
      'color' => 'red',
      'details' => 'data extra'
    ];

    $query = $builder->insert();
    $query->setTable($table)
      ->setValues($data);

    $query = $this->queryToOneLine($query);
    $computed_values = array_values($builder->getValues());
    $values = array_values($data);

    for ($i = 0, $length = count($values); $i < $length; $i++) {
      $this->assertEquals($values[$i], $computed_values[$i]);
    }

    $this->assertEquals(
      $query,
      "INSERT INTO `$table` ( " . $this->arrayTokeys($data) . " ) VALUES ( " . $this->arrayToValues($values) . " )"
    );
  }

  /**
   * @test
   */
  public function queryWithPartitions()
  {
    $builder = new MySqlBuilder();

    $query = $builder->select();

    $query
      ->setTable(["A" => "table"], ["pa1", "pa2"])
      ->innerJoin(["B" => "table2"], "B.id", "A.id_b", fn () => ["pb1", "pb2", "pb3"])
      ->leftJoin(["C" => "table3"], "C.id", "B.id_c", fn () => ["pc20", "pc30", "pc3"])
      ->rightJoin(["D" => "table4"], "D.id", "C.id_d", fn () => ["pd100", "pd200", "pd300"]);

    $this->assertEquals(
      $this->queryToOneLine($query),
      $this->queryToOneLine("SELECT * FROM
          `table` PARTITION(pa1, pa2) AS `A`
          INNER JOIN `table2` PARTITION(pb1, pb2, pb3) AS `B` ON (`A`.`id_b` = `B`.`id`)
          LEFT JOIN `table3` PARTITION(pc20, pc30, pc3) AS `C` ON (`B`.`id_c` = `C`.`id`)
          RIGHT JOIN `table4` PARTITION(pd100, pd200, pd300) AS `D` ON (`C`.`id_d` = `D`.`id`)")
    );
  }

  /**
   * @test
   */
  public function queryLongTest()
  {
    $builder = new MySqlBuilder();
    $query = $builder->select();

    $query
      ->setTable('ACCOUNTS')
      ->addColumn('Id', 'Index')
      ->addColumn('Account')
      ->addColumnCustom('Avg', 'SUM(column_a) / COUNT(column_a)')
      ->addColumnFunction('status', 'IF', ['`status` = 1', 'Active', 'Inactive']);

    $this->assertEquals(
      $this->queryToOneLine($query),
      $this->queryToOneLine(
        "SELECT
            `Id` AS `Index`,
            `Account`,
            SUM(column_a) / COUNT(column_a) AS `Avg`,
            IF(`status` = 1, Active, Inactive) AS `status`
          FROM `ACCOUNTS`"
      )
    );
  }

  /**
   * @test
   */
  public function queryFunctionTest()
  {
    $builder = new MySqlBuilder();
    $query = $builder->select();

    $query->setTable('ACCOUNTS');
    $query->setColumns(['id']);
    $query->setValueAsColumn('SUM(updated_percent) / COUNT(id)', 'efectivity');
    $query->addColumns([
      'valid_id' => 'IF(updated_percent IS NOT NULL, updated_percent * 100, 0)',
    ]);
    $query
      ->where()
      ->isNotNull("updated_percent")
      ->equals("status", 2)
      ->end();

    $this->assertEquals(
      $this->queryToOneLine($query),
      $this->queryToOneLine(
        "SELECT
              `id`,
              SUM(updated_percent) / COUNT(id) AS `efectivity`,
              IF( updated_percent IS NOT NULL, updated_percent * 100, 0 ) AS `valid_id`
          FROM
              `ACCOUNTS`
          WHERE
              (`status` = 2)
              AND (`updated_percent` IS NOT NULL)"
      )
    );
  }
}
