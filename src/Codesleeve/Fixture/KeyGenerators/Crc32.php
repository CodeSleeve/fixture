<?php namespace Codesleeve\Fixture\KeyGenerators;

/**
 * CRC32 Key Generator
 */
class Crc32
{

    /**
     * {@inheritdoc}
     */
    public function generateKey($value, $tableName = null)
    {
        return sprintf('%u', crc32($tableName . $value));
    }
}
