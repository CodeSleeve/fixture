<?php namespace Codesleeve\Fixture\KeyGenerators;

/**
 * Generates a key for a given value using the SHA1 hash.
 */
class SHA1KeyGenerator implements KeyGeneratorInterface
{
    /**
     * The length to trim the sha hash to
     *
     * @var int
     */
    private $length = 10;

    /**
     * Constructor method
     *
     * @param int $length
     */
    public function __construct($length = 10)
    {
        $this->length = $length;
    }

    /**
     * {@inheritDoc}
     */
    public function generateKey($value)
    {
        $hash = sha1($value);
        $integerHash = base_convert($hash, 16, 10);

        return (int) substr($integerHash, 0, $this->length);
    }
}
