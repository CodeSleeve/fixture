<?php

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
	 * Test that the up method throws an invalid fixture location exception
	 * for fixture locations that don't exist.
	 *
     * @test
	 * @expectedException Codesleeve\Fixture\Exceptions\InvalidFixtureLocationException
	 * @return void
	 */
	public function it_should_throw_an_exception_if_the_fixture_path_does_not_exist()
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
    public function it_should_throw_an_exception_if_the_fixture_is_not_an_array()
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
    public function it_should_throw_an_exception_if_the_fixture_name_does_not_exist()
    {
        $this->fixture->setConfig(array('location' => __DIR__ . '/fixtures/standard'));
        $this->fixture->setFixtures([]);

        $this->fixture->foo();
    }
}