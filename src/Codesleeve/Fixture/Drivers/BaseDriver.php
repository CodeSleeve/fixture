<?php namespace Codesleeve\Fixture\Drivers;

abstract class BaseDriver
{

    /**
     * An array of tables that have had fixture data loaded into them.
     *
     * @var array
     */
    protected $tables = array();
    
    /**
     * Truncate a table.
     *
     * @param array $tables The tables to truncate, null for only tables
     * that have been inserted into in this instance.
     * @return void
     */
    public function truncate(array $tables = null)
    {
        if (null === $tables) {
            $tables = $this->tables;
        }
        
        foreach ($tables as $table) {
            $this->db->query("DELETE FROM $table");
        }
        
        $this->tables = array_diff($this->tables, $tables);
    }

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

        return (int)substr($integerHash, 0, 8);
    }
    
    /**
     * Set tables that the driver to process
     */
    protected function setTables(array $tables)
    {
        
    }
}
