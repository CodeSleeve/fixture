<?php namespace Codesleeve\FixtureL4\Repositories;

interface RepositoryInterface {

	/**
	 * Build a fixture record using the passed in values.
	 *
	 * @param  string $tableName
	 * @param  array $records   
	 * @return Model             
	 */
	public function buildRecords($tableName, $records);

	/**
	 * Truncate a table.
	 * 
	 * @return void           
	 */
	public function truncate();
}