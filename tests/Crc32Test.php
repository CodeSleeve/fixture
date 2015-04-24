<?php namespace Codesleeve\Fixture\KeyGenerators;

use Codesleeve\Fixture\KeyGenerators\Crc32;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Codesleeve\Fixture\KeyGenerators\Crc32
 */
class Crc32Test extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::generateKey
     * @covers ::__construct
     */
    public function itShouldReturnAValidCrc32Int()
    {
        $generator = new Crc32();
        $key = $generator->generateKey('foo');
        
        if (PHP_INT_MAX > pow(2, 32)) {
            // 64-bit
            $this->assertEquals(2356372769, $key);
        } else {
            // 32-bit
            $this->assertEquals(208889122, $key);
        }
        
        $generator = new Crc32(10);
        $key = $generator->generateKey('foo', 'bar');
        $this->assertEquals(5, $key);
    }
}
