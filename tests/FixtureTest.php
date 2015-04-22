<?php namespace Codesleeve\Fixture;

use PHPUnit_Framework_TestCase;
use Codesleeve\Fixture\Fixture;
use Mockery as m;

class FixtureTest extends PHPUnit_Framework_TestCase
{
   /**
     * An instance of the fixture class.
     *
     * @var Fixture
     */
    protected $fixture;

    /**
     * setUp method.
     */
    public function setUp()
    {
        $this->fixture = Fixture::getInstance();
    }

    /**
     * tearDown method.
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Test that the fixture class is able to generate a single instance
     * of itself.
     *
     * @test
     * @return void
     */
    public function itShouldCreateOnlyASingleInstanceOfItself()
    {
        $fixture = Fixture::getInstance();

        $this->assertInstanceOf('Codesleeve\Fixture\Fixture', $fixture);
        $this->assertSame($this->fixture, $fixture);
    }

   /**
    * Test that the up method throws an invalid fixture location exception
    * for fixture locations that don't exist.
    *
     * @test
    * @expectedException Codesleeve\Fixture\Exceptions\InvalidFixtureLocationException
    * @return void
    */
    public function itShouldThrowAnExceptionIfTheFixturePathDoesNotExist()
    {
         $this->fixture->setConfig(array('location' => ''));
         $this->fixture->up();
    }

   /**
     * Test that that up method throws an invalid fixture error if one of the fixtures
     * is not an array
     *
     * @test
     * @expectedException Codesleeve\Fixture\Exceptions\InvalidFixtureDataException
     * @return void
     */
    public function itShouldThrowAnExceptionIfTheFixtureIsNotAnArray()
    {
        $this->fixture->setConfig(array('location' => __DIR__ . '/invalid_fixtures'));
        $this->fixture->up();
    }

    /**
     * Test that an an exception is thrown when trying to access a fixture that
     * does not exist
     *
     * @test
     * @expectedException Codesleeve\Fixture\Exceptions\InvalidFixtureNameException
     * @return void
     */
    public function itShouldThrowAnExceptionIfTheFixtureNameDoesNotExist()
    {
        $this->fixture->setConfig(array('location' => __DIR__ . '/fixtures/standard'));
        $this->fixture->setFixtures(array());

        $this->fixture->foo();
    }

    /**
     * Test that fake fixture data (using the Faker library) can be generated.
     * The desired behavior is that the 'fake' method of Fixture will act as a proxy
     * to the Faker library.
     *
     * @test
     * @return void
     */
    public function itShouldAbleToGenerateFakeFixtureData()
    {
        $word = Fixture::fake('word');
        $number = Fixture::fake('numberBetween', 1, 1);

        $this->assertInternalType('string', $word);
        $this->assertEquals(1, $number);
    }
}
