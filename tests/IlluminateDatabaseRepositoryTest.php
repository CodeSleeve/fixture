<?php  

use Codesleeve\Fixture\Fixture;
use Codesleeve\Fixture\Repositories\IlluminateDatabaseRepository;
use Illuminate\Support\Str;
use Mockery as m;

class IlluminateDatabaseRepositoryTest extends PHPUnit_Framework_TestCase
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
        $this->fixture->setConfig(['location' => __DIR__ . '/fixtures/orm']);
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
        $this->fixture->setConfig(['location' => __DIR__ . '/fixtures/orm']);
        $this->fixture->up(array('users'));

        $this->assertEquals('Travis', $this->fixture->users('Travis')->first_name);
        $this->assertCount(1, $this->fixture->getFixtures());
	}

    /**
     * Test that extra join columns for a HABTM fixture are being populated.
     *
     * @test
     * @return void
     */
    public function it_should_populate_fixture_join_column_data()
    {
        $this->fixture->setConfig(['location' => __DIR__ . '/fixtures/orm']);
        $this->fixture->up(array('users', 'roles'));

        $this->assertEquals(1, $this->fixture->users('Travis')->roles[0]->pivot->active);
        $this->assertEquals(0, $this->fixture->users('Travis')->roles[1]->pivot->active);
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
        $str = new Str;
        $this->fixture = Fixture::getInstance();
        $repository = new IlluminateDatabaseRepository($db, $str);
        $this->fixture->setRepository($repository);

        // Bootstrap Eloquent
        $sqliteConnection = new Illuminate\Database\SQLiteConnection($db);
        $resolver = new Illuminate\Database\ConnectionResolver(array('sqlite' => $sqliteConnection));
        $resolver->setDefaultConnection('sqlite');
        Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);
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
        $db->exec("CREATE TABLE IF NOT EXISTS roles_users (id INTEGER PRIMARY KEY, role_id INTEGER, user_id INTEGER, active INTEGER DEFAULT 0)");
        $db->exec("CREATE TABLE IF NOT EXISTS games (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT)");

        return $db;
    }
}
