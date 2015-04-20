<?php

namespace Codesleeve\Fixture\KeyGenerators;

/**
 * Generates a key for a given value using the crc32 checksum, this is identical to how rails generates
 * fixture's keys.
 */
class Crc32KeyGenerator implements KeyGeneratorInterface
{
    /**
     * Constructor method
     *
     */
    public function __construct()
    {
        define(__NAMESPACE__.'\MAX_ID', pow(2, 30) - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function generateKey($value)
    {
        return (int) crc32($value) % MAX_ID;
    }
}
