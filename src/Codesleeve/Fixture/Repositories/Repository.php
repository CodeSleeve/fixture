<?php namespace Codesleeve\FixtureL4\Repositories;

class Repository
{
	/**
	 * Generate an integer hash of a string.
	 * We'll use this method to convert a fixture's name into the
	 * primary key of it's corresponding database table record.
	 * 
	 * @param  string $value - This should be the name of the fixture.
	 * @return integer      
	 */
	protected function generateKey($value)
	{
		$hash = sha1($value);
		$integerHash = base_convert($hash, 16, 10);
		
		return (int)substr($integerHash, 0, 10);
	}
}