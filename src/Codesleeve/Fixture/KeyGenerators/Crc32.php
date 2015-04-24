<?php namespace Codesleeve\Fixture\KeyGenerators;

/**
 * CRC32 Key Generator
 */
class Crc32
{
    /**
     * @var int $max The maximum supported CRC32 value
     */
    private $max;
    
    /**
     * Initialize max key size
     *
     * @param int $max
     */
    public function __construct($max = PHP_INT_MAX)
    {
        $this->max = $max;
    }
    
    /**
     * {@inheritdoc}
     */
    public function generateKey($value, $tableName = null)
    {
        return crc32($tableName . $value) % $this->max;
    }
}
