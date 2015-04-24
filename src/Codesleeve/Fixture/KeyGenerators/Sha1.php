<?php namespace Codesleeve\Fixture\KeyGenerators;

/**
 * SHA1 Key Generator
 */
class Sha1
{

    /**
     * @var int The sha1 return length
     */
    private $length;
    
    /**
     * Initialize the key length
     *
     * @param int $length
     */
    public function __construct($length = 8)
    {
        $this->length = $length;
    }
    
    /**
     * {@inheritdoc}
     */
    public function generateKey($value, $tableName = null)
    {
        $hash = sha1($tableName . $value);
        $integerHash = base_convert($hash, 16, 10);
        
        return (int) substr($integerHash, 0, $this->length);
    }
}
