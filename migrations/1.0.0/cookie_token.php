<?php

use Phalcon\Db\Column;
use Phalcon\Db\Index;
use Phalcon\Mvc\Model\Migration;

/**
 * Class CookieTokenMigration_101
 */
class CookieTokenMigration_101 extends Migration
{
	/**
	 * Define the table structure
	 *
	 * @return void
	 */
	public function morph()
	{
		$this->morphTable('cookie_token', [
				'columns' => [
					new Column(
						'id',
						[
							'type'          => Column::TYPE_INTEGER,
							'notNull'       => true,
							'autoIncrement' => true,
							'size'          => 10,
							'first'         => true
						]
					),
					new Column(
						'token',
						[
							'type'    => Column::TYPE_CHAR,
							'notNull' => true,
							'size'    => 120,
							'after'   => 'id'
						]
					),
					new Column(
						'user_id',
						[
							'type'     => Column::TYPE_INTEGER,
							'unsigned' => true,
							'notNull'  => true,
							'size'     => 10,
							'after'    => 'token'
						]
					),
					new Column(
						'created_at',
						[
							'type'    => Column::TYPE_TIMESTAMP,
							'default' => "CURRENT_TIMESTAMP",
							'notNull' => true,
							'size'    => 1,
							'after'   => 'user_id'
						]
					)
				],
				'indexes' => [
					new Index('PRIMARY', ['id'], 'PRIMARY'),
					new Index('token_cookie_token_uindex', ['token'], 'UNIQUE')
				],
				'options' => [
					'TABLE_TYPE'      => 'BASE TABLE',
					'AUTO_INCREMENT'  => '8',
					'ENGINE'          => 'InnoDB',
					'TABLE_COLLATION' => 'utf8_general_ci'
				],
			]
		);
	}

	/**
	 * Run the migrations
	 *
	 * @return void
	 */
	public function up()
	{

	}

	/**
	 * Reverse the migrations
	 *
	 * @return void
	 */
	public function down()
	{

	}

}
