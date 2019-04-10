<?php

	use PHPUnit\Framework\TestCase;
	use NilPortugues\Sql\QueryBuilder\Builder\MySqlBuilder;
	/**
	 * Class Skynets.
	 */
	class Skynet extends TestCase {

		/**
		 * @var MySqlBuilder
		 */
		protected $builder;

		public function setUp() {
			 $this->builder = new MySqlBuilder();
		}

		/**
		 * @test
		 */
		public function test() {

			$builder = new MySqlBuilder();
			$query = $builder->select()
				->setTable('user');

			$query->setColumns([
						'userId'   => 'user_id',
						'username' => 'name',
						'email'    => 'email',
						'created_at'
				])
				->orderBy('user_id')
				->leftJoin(
					'news', //join table
					'user_id', //origin table field used to join
					'author_id', //join column
					['newsTitle' => 'title', 'body', 'created_at', 'updated_at']
				)
				->on()
				->equals('author_id', 1); //enforcing a condition on the join column

			$query
				->where()
				->greaterThan('user_id', 5)
				->notLike('username', 'John')
				->end();

			$query
				->orderBy('created_at');

			echo "query: ";
			echo $builder->writeFormatted($query);
		}
	}