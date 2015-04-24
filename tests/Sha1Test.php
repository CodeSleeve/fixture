<?php namespace Codesleeve\Fixture\KeyGenerators;

use Codesleeve\Fixture\KeyGenerators\Sha1;
use PHPUnit_Framework_TestCase;

/**
 * @coversDefaultClass Codesleeve\Fixture\KeyGenerators\Sha1
 */
class Sha1Test extends PHPUnit_Framework_TestCase
{

    /**
     * @test
     * @covers ::generateKey
     * @covers ::__construct
     */
    public function itShouldReturnAValidSha1Int()
    {
        $generator = new Sha1();
        $key = $generator->generateKey('foo');
        $this->assertEquals(68123873, $key);
        
        $generator = new Sha1(4);
        $key = $generator->generateKey('foo', 'bar');
        $this->assertEquals(5498, $key);
    }
}
