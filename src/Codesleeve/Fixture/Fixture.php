<?php namespace Codesleeve\Fixture;

use Codesleeve\Fixture\Drivers\DriverInterface;
use Faker\Generator;

/**
 * A framework agnostic, simple (yet elegant) fixture library for php.
 *
 * @package Codesleeve/Fixture
 * @version v1.0.0
 * @author Travis Bennett <tandrewbennett@hotmail.com>
 * @link http://travisbennett.net
 */
class Fixture
{
    /**
     * An array of eloquent collections (one for each loaded fixture).
     *
     * @var array
     */
    protected $fixtures;

    /**
     * An array of configuration options.
     *
     * @var Array
     */
    protected $config;

    /**
     * The ORM specific database driver that's being used.
     *
     * @var Driver
     */
    protected $driver;

    /**
     * An instance of the Faker library
     * @var Generator
     */
    protected static $faker;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instances of this class.
     *
     * @param  array $config
     * @param  DriverInterface $driver
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance(array $config = array(), DriverInterface $driver = null)
    {
        static $instance = null;

        if (null === $instance) {
            $instance = new static();
        }

        if ($config) {
            $instance->config = $config;
        }

        if ($driver) {
            $instance->driver = $driver;
        }

        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     *
     * @return void
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * *Singleton* instance.
     *
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the *Singleton*
     * instance.
     *
     * @return void
     */
    private function __wakeup()
    {
    }

    /**
     * Setter method for the configuration array used by
     * the fixture.
     *
     * @param Array $configArray
     */
    public function setConfig($configArray)
    {
        $this->config = $configArray;
    }

    /**
     * Getter method for the configuration array used by
     * the fixture.
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Setter method for the driver instance used by
     * the fixture.
     *
     * @param Drivers\DriverInterface $driver
     */
    public function setDriver(Drivers\DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Getter method for the driver instance used by
     * the fixture.
     *
     * @return Drivers\DriverInterface
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Return all fixtures.
     *
     * @return array
     */
    public function getFixtures()
    {
        return $this->fixtures;
    }

    /**
     * Set all fixtures.
     *
     * @param array $fixtures
     */
    public function setFixtures(array $fixtures)
    {
        $this->fixtures = $fixtures;
    }

    /**
     * Handle dynamic method calls to this class.
     * This allows us to return fixture objects via method invocation.
     *
     * @param  string $name
     * @param  array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!array_key_exists($name, $this->fixtures)) {
            throw new Exceptions\InvalidFixtureNameException("Fixture: $name does not exist", 1);
        }

        $fixture = $this->fixtures[$name];

        if ($arguments && array_key_exists($arguments[0], $fixture)) {
            return $fixture[$arguments[0]];
        }

        return $fixture;
    }

    /**
     * Build fixtures.
     *
     * @param  array $fixtures
     * @throws Exceptions\InvalidFixtureLocationException
     * @return void
     */
    public function up($fixtures = array())
    {
        $location = $this->config['location'];

        if (!is_dir($location)) {
            throw new Exceptions\InvalidFixtureLocationException(
                "Could not find fixtures folder, please make sure $location exists",
                1
            );
        }

        $this->loadFixtures($fixtures);
    }

    /**
     * Destroy fixtures.
     *
     * @return void
     */
    public function down()
    {
        $this->driver->truncate();
        $this->fixtures = array();
    }

    /**
     * Create fake data using Faker.
     *
     * @return mixed
     */
    public static function fake()
    {
        static::bootFaker();
        $params = func_get_args();
        $method = array_shift($params);

        return call_user_func_array(array(static::$faker, $method), $params);
    }

    /**
     * Create an instance of the faker method (if one doesn't already exist)
     * and then hang it on this class as a static property.
     *
     * @return void
     */
    protected static function bootFaker()
    {
        static::$faker = static::$faker ?: \Faker\Factory::create();
    }

    /**
     * Load fixtures.
     *
     * @param  array $fixtures
     * @return void
     */
    protected function loadFixtures($fixtures)
    {
        if ($fixtures) {
            $this->loadSomeFixtures($fixtures);

            return;
        }

        $this->loadAllFixtures();
    }

    /**
     * Load all fixtures from the fixture location.
     *
     * @return void
     */
    protected function loadAllFixtures()
    {
        $fixtures = glob("{$this->config['location']}/*.php");

        foreach ($fixtures as $fixture) {
            $this->loadFixture($fixture);
        }
    }

    /**
     * Load a only a subset of fixtures from the fixtures folder.
     *
     * @param  array $selectedFixtures
     * @return void
     */
    protected function loadSomeFixtures($selectedFixtures)
    {
        $fixtures = glob("{$this->config['location']}/*.php");

        foreach ($fixtures as $fixture) {
            $tableName = basename($fixture, '.php');

            if (in_array($tableName, $selectedFixtures)) {
                $this->loadFixture($fixture);
            }
        }
    }

    /**
     * Load a fixture's data into the database.
     * We'll also store it inside the fixtures property for easy
     * access as an array element or class property from our tests.
     *
     * @param  string $fixture
     * @return void
     */
    protected function loadFixture($fixture)
    {
        $tableName = basename($fixture, '.php');
        $records = include $fixture;

        if (!is_array($records)) {
            throw new Exceptions\InvalidFixtureDataException(
                "Invalid fixture: $fixture, please ensure this file returns an array of data.",
                1
            );
            
        }

        $this->fixtures[$tableName] = $this->driver->buildRecords($tableName, $records);
    }
}
