<?php namespace Codesleeve\Fixture\Drivers;

use Codesleeve\Fixture\KeyGenerators\KeyGeneratorInterface;
use Codesleeve\Fixture\KeyGenerators\Sha1;

abstract class BaseDriver
{
    protected $keyGenerator;
    
    /**
     * Constructor method
     *
     * @param KeyGeneratorInterface $keyGenerator
     */
    public function __construct(KeyGeneratorInterface $keyGenerator = null)
    {
        if (null === $keyGenerator) {
            $keyGenerator = new Sha1();
        }
        $this->keyGenerator = $keyGenerator;
    }
    /**
     * Truncate a table.
     *
     * @return void
     */
    public function truncate()
    {
        foreach ($this->tables as $table) {
            $this->db->query("DELETE FROM $table");
        }

        $this->tables = array();
    }

    /**
     * Generate a key using the provided key generator
     *
     * @param string $value
     * @param string $tableName
     */
    protected function generateKey($value, $tableName = null)
    {
        return $this->keyGenerator->generateKey($value, $tableName);
    }
}
