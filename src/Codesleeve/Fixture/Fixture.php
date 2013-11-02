<?php namespace Codesleeve\FixtureL4;

class Fixture extends Singleton
{
	/**
	 * An array of eloquent collections (one for each loaded fixture).
	 * 
	 * @var array
	 */
	protected $fixtures;

	/**
     * The ORM specific database repository that's being used.
     * 
     * @var Repository
     */
    protected $repository;

    /**
     * An array of configuration options.
     * 
     * @var Array
     */
    protected $config = ['location' => ''];

	/**
	 * Build fixtures.
	 * 
	 * @param  array $fixtures 
	 * @return void
	 */
	public function up($fixtures = [])
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
     * Handle dynamic method calls to this class.
     * This allows us to return fixture objects via method invocation.
     * 
     * @param  string $name      
     * @param  array $arguments 
     * @return mixed            
     */
    public function __call($name, $arguments)
    {
        $fixture = array_key_exists($name, $this->fixtures) ? $this->fixtures[$name] : null;

        if ($arguments && array_key_exists($arguments[0], $fixture)) 
        {
        	return $fixture[$arguments[0]];
        }

        return $fixture;
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
	 * @param  array $fixture 
	 * @return void
	 */
	protected function loadFixture($fixture)
	{
		$tableName = basename($fixture, '.php');
		$records = include $fixture;
		$this->fixtures[$tableName] = $this->repository->buildRecords($tableName, $records);
	}
}