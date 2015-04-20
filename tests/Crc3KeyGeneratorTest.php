<?php

use Codesleeve\Fixture\KeyGenerators\Crc32KeyGenerator;

class Crc32KeyGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testReturnsValidKey()
    {
        $generator = new Crc32KeyGenerator();

        $key = $generator->generateKey('foo');

        $this->assertEquals(9, strlen($key));
        $this->assertEquals(208889123, $key);
    }
}
