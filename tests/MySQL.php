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


		public function test() {

			$builder = new MySqlBuilder();
			$query = $builder->select()
				->setTable('user');

			$query->setColumns([
						'userId'   => 'user.user_id',
						'username' => 'user.name',
						'email'    => 'user.email',
						'user.created_at'
				])
				->orderBy('user_id')
				->leftJoin(
					'news', //join table
					'user.user_id', //origin table field used to join
					'news.author_id' //join column
				)
				->on()
				->equals('news.author_id', 1); //enforcing a condition on the join column

			$query
				->where()
				->greaterThan('user.user_id', 5)
				->notLike('user.username', 'John')
				->end();

			$query
				->orderBy('created_at');


		}


		/**
		 * @test
		 */
		public function test2()
		{

			$builder = new MySqlBuilder();
			$query = $builder->select()
				->setTable('user');

			echo "query: ";
			echo $builder->writeFormatted($query);
		}
	}