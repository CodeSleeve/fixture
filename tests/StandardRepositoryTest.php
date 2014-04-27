<?php  

use Codesleeve\Fixture\Fixture;
use Codesleeve\Fixture\Repositories\StandardRepository;
use Mockery as m;

class StandardRepositoryTest extends PHPUnit_Framework_TestCase
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
        $this->buildFixture();
    }

    /**
     * tearDown method.
     */
    public function tearDown()
    {
        $this->fixture->down();
        m::close();
    }

	/**
	 * Test that the up method will populate all fixtures when called
	 * with an empty parameter list.
	 *
     * @test
	 * @return void
	 */
	public function it_should_populate_all_fixtures()
	{
        $this->fixture->setConfig(array('location' => __DIR__ . '/fixtures/standard'));
        $this->fixture->up();

        $this->assertEquals('Travis', $this->fixture->users('Travis')->first_name);
        $this->assertEquals('Diablo 3', $this->fixture->games('Diablo3')->title);
        $this->assertEquals('root', $this->fixture->roles('root')->name);
        $this->assertCount(3, $this->fixture->getFixtures());
	}

	/**
	 * Test that the up method will only populate fixtures that 
	 * are supplied to it via parameters.
	 *
     * @test
	 * @return void
	 */
	public function it_should_populate_only_some_fixtures()
	{
		$this->fixture->setConfig(array('location' => __DIR__ . '/fixtures/standard'));
        $this->fixture->up(array('users'));

        $this->assertEquals('Travis', $this->fixture->users('Travis')->first_name);
        $this->assertCount(1, $this->fixture->getFixtures());
	}

    /**
     * Build a fixture instance.
     *
     * @return void
     */
    protected function buildFixture()
    {
        if ($this->fixture) {
            return;
        }

        $db = $this->buildDB();
        $this->fixture = Fixture::getInstance();
        $repository = new StandardRepository($db);
        $this->fixture->setRepository($repository);
    }

    /**
     * Build a PDO instance.
     *
     * @return PDO
     */
    protected function buildDB()
    {
        $db = new PDO('sqlite::memory:');
        $db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY, first_name TEXT, last_name TEXT)");
        $db->exec("CREATE TABLE IF NOT EXISTS roles (id INTEGER PRIMARY KEY, name TEXT)");
        $db->exec("CREATE TABLE IF NOT EXISTS games (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT)");

        return $db;
    }
}
