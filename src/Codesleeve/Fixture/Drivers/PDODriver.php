<?php namespace Codesleeve\Fixture\Drivers;

use Codesleeve\Fixture\KeyGenerators\KeyGeneratorInterface;
use Codesleeve\Fixture\KeyGenerators\SHA1KeyGenerator;
use PDO;

class PDODriver
{
    /**
     * A PDO connection instance.
     *
     * @var PDO
     */
     protected $db;

    /**
     * An array of tables that have had fixture data loaded into them.
     *
     * @var array
     */
     protected $tables = array();

     /**
 	 * Constructor method
 	 *
 	 * @param  DatabaseManager $db
 	 */
    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }
    /**
     * Truncate a table.
     */
    public function truncate()
    {
        foreach ($this->tables as $table) {
            $this->db->query("DELETE FROM $table");
        }

        $this->tables = array();
    }

    /**
     * Generate an integer hash of a string.
     * We'll use this method to convert a fixture's name into the
     * primary key of it's corresponding database table record.
     *
     * @param string $value - This should be the name of the fixture.
     *
     * @return int
     */
     protected function generateKey($value)
     {
         $hash = sha1($value);
         $integerHash = base_convert($hash, 16, 10);
         return (int)substr($integerHash, 0, 10);
     }
}
