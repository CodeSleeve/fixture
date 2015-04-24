<?php namespace Codesleeve\Fixture\KeyGenerators;

interface KeyGeneratorInterface
{

    /**
     * Generate a key
     *
     * @param string $value
     * @param string $tableName
     * @return string|int
     */
    public function generateKey($value, $tableName = null);
}
