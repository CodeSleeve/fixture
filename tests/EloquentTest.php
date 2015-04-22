<?php namespace Codesleeve\Fixture;

use PHPUnit_Framework_TestCase;
use Codesleeve\Fixture\Fixture;
use Codesleeve\Fixture\Drivers\Eloquent;
use Illuminate\Support\Str;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model;
use Mockery as m;
use PDO;

class EloquentTest extends PHPUnit_Framework_TestCase
{
    /**
     * An instance of the fixture class.
     *
     * @var Fixture
     */
    protected $fixture;

    /**
     * A PDO instance.
     *
     * @var PDO
     */
    protected $db;

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
        $this->db->query("DELETE FROM users");
        $this->db->query("DELETE FROM roles");
        $this->db->query("DELETE FROM games");
        $this->fixture->setFixtures(array());
        m::close();
    }

    /**
     * Test that the up method will populate all fixtures when called
     * with an empty parameter list.
     *
     * @test
     * @return void
     */
    public function itShouldPopulateAllFixtures()
    {
        $this->fixture->setConfig(array('location' => __DIR__ . '/fixtures/orm'));
        $this->fixture->up();

        list($userCount, $roleCount, $gameCount) = $this->getRecordCounts();

        $this->assertEquals('Travis', $this->fixture->users('Travis')->first_name);
        $this->assertEquals('Diablo 3', $this->fixture->games('Diablo3')->title);
        $this->assertEquals('root', $this->fixture->roles('root')->name);
        $this->assertEquals(2, $userCount);
        $this->assertEquals(2, $roleCount);
        $this->assertEquals(2, $gameCount);
        $this->assertCount(3, $this->fixture->getFixtures());
    }

    /**
     * Test that the up method will only populate fixtures that
     * are supplied to it via parameters.
     *
     * @test
     * @return void
     */
    public function itShouldPopulateOnlySomeFixtures()
    {
        $this->fixture->setConfig(array('location' => __DIR__ . '/fixtures/orm'));
        $this->fixture->up(array('users'));

        list($userCount, $roleCount, $gameCount) = $this->getRecordCounts();

        $this->assertEquals('Travis', $this->fixture->users('Travis')->first_name);
        $this->assertEquals(2, $userCount);
        $this->assertEquals(0, $roleCount);
        $this->assertEquals(0, $gameCount);
        $this->assertCount(1, $this->fixture->getFixtures());
    }

    /**
     * Test that the down method will truncate all current fixture table data
     * and empty the fixtures array.
     *
     * @test
     * @return void
     */
    public function itShouldTruncateAllFixtures()
    {
        $this->fixture->setConfig(array('location' => __DIR__ . '/fixtures/orm'));
        $this->fixture->up();
        $this->fixture->down();

        list($userCount, $roleCount, $gameCount) = $this->getRecordCounts();

        $this->assertEmpty($this->fixture->getFixtures());
        $this->assertEquals(0, $userCount);
        $this->assertEquals(0, $roleCount);
        $this->assertEquals(0, $gameCount);
    }

    /**
     * Test that extra join columns for a HABTM fixture are being populated.
     *
     * @test
     * @return void
     */
    public function itShouldPopulateFixtureJoinColumnData()
    {
        $this->fixture->setConfig(array('location' => __DIR__ . '/fixtures/orm'));
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

        $this->db = $this->buildDB();
        $str = new Str;
        $this->fixture = Fixture::getInstance();
        $repository = new Eloquent($this->db, $str);
        $this->fixture->setDriver($repository);

        // Bootstrap Eloquent
        $sqliteConnection = new SQLiteConnection($this->db);
        $resolver = new ConnectionResolver(array('sqlite' => $sqliteConnection));
        $resolver->setDefaultConnection('sqlite');
        Model::setConnectionResolver($resolver);
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
        $db->exec(
            "CREATE TABLE IF NOT EXISTS roles_users"
            . " (id INTEGER PRIMARY KEY, role_id INTEGER, user_id INTEGER, active INTEGER DEFAULT 0)"
        );
        $db->exec("CREATE TABLE IF NOT EXISTS games (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT)");

        return $db;
    }

    /**
     * Helper method to return the current record count in each
     * fixture table.
     *
     * @return array
     */
    protected function getRecordCounts()
    {
        $userQuery = $this->db->query('SELECT COUNT(*) AS count from users');
        $userCount = $userQuery->fetchColumn(0);

        $roleQuery = $this->db->query('SELECT COUNT(*) AS count from roles');
        $roleCount = $roleQuery->fetchColumn(0);

        $gameQuery = $this->db->query('SELECT COUNT(*) AS count from games');
        $gameCount = $gameQuery->fetchColumn(0);

        return array($userCount, $roleCount, $gameCount);
    }
}
