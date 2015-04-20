<?php namespace Codesleeve\Fixture\KeyGenerators;

/**
 * Generates a key for a given value.
 *  */
interface KeyGeneratorInterface
{
    /**
     * Generate a cache key for a given value.
     *
     * @param mixed $value
     *
     * @return string|int
     */
    public function generateKey($value);
}
