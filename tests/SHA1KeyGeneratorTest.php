<?php

use Codesleeve\Fixture\KeyGenerators\SHA1KeyGenerator;

class SHA1KeyGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testReturnsValidKey()
    {
        $generator = new SHA1KeyGenerator();

        $key = $generator->generateKey('foo');

        $this->assertEquals(10, strlen($key));
        $this->assertEquals(6812387308, $key);
    }

    public function testReturnsValidKeyWithCustomLength()
    {
        $generator = new SHA1KeyGenerator(8);

        $this->assertEquals(8, strlen($generator->generateKey('foo')));
    }
}
