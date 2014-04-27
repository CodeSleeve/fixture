<?php namespace Codesleeve\Fixture;

use Codesleeve\Fixture\Repositories\RepositoryInterface;
use Faker\Generator; 

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
    protected $config = array('location' => '');

	/**
     * The ORM specific database repository that's being used.
     *
     * @var Repository
     */
    protected $repository;

    /**
     * An instance of the Faker library
     * @var Generator
     */
    protected $faker;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instances of this class.
     *
     * @param  array $config
     * @param  RepositoryInterface $repository
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance(array $config = array('location' => ''), RepositoryInterface $repository = null)
    {
        static $instance = null;

        if (null === $instance) 
        {
        	$faker = \Faker\Factory::create();
            $instance = new static($config, $faker, $repository);
        }

        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     * 
     * @param  array $config
     * @param  Generator $faker
     * @param  RepositoryInterface $repository
     * @return void
     */
    protected function __construct(array $config, Generator $faker, RepositoryInterface $repository = null)
    {
    	$this->config = $config;
    	$this->faker = $faker;
    	$this->repository = $repository;
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
	 * Setter method for the repository instance used by
	 * the fixture.
	 *
	 * @param Repositories\RepositoryInterface $repository
	 */
	public function setRepository(Repositories\RepositoryInterface $repository)
	{
		$this->repository = $repository;
	}

	/**
	 * Getter method for the repository instance used by
	 * the fixture.
	 *
	 * @return Repositories\RepositoryInterface
	 */
	public function getRepository()
	{
		return $this->repository;
	}

	/**
	 * Setter method for the faker instance used by
	 * the fixture.
	 *
	 * @param Generator $faker
	 */
	public function setFaker(Generator $faker)
	{
		$this->faker = $faker;
	}

	/**
	 * Getter method for the faker instance used by
	 * the fixture.
	 *
	 * @return Generator
	 */
	public function getFaker()
	{
		return $this->faker;
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
			throw new Exceptions\InvalidFixtureLocationException("Could not find fixtures folder, please make sure $location exists", 1);
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
		$this->repository->truncate();
        $this->fixtures = array();
	}

    /**
     * Create fake data using Faker.
     * 
     * @return mixed
     */
    public function fake()
    {
    	$params = func_get_args();
    	$method = array_shift($params);

    	return call_user_func_array(array($this->faker, $method), $params);
    }

	/**
	 * Load fixtures.
	 *
	 * @param  array $fixtures
	 * @return void
	 */
	protected function loadFixtures($fixtures)
	{
		if ($fixtures)
		{
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

		foreach ($fixtures as $fixture)
		{
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
			throw new Exceptions\InvalidFixtureDataException("Invalid fixture: $fixture, please ensure this file returns an array of data.", 1);
			
		}

		$this->fixtures[$tableName] = $this->repository->buildRecords($tableName, $records);
	}
}