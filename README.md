#Fixture
[![Build Status](https://travis-ci.org/CodeSleeve/fixture.png?branch=development)](https://travis-ci.org/CodeSleeve/fixture)
[![Latest Stable Version](https://poser.pugx.org/codesleeve/fixture/v/stable.png)](https://packagist.org/packages/codesleeve/fixture)
[![Total Downloads](https://poser.pugx.org/codesleeve/fixture/downloads.png)](https://packagist.org/packages/codesleeve/fixture)
[![Latest Unstable Version](https://poser.pugx.org/codesleeve/fixture/v/unstable.png)](https://packagist.org/packages/codesleeve/fixture)
[![License](https://poser.pugx.org/codesleeve/fixture/license.png)](https://packagist.org/packages/codesleeve/fixture)

A framework agnostic, simple (yet elegant) fixture library for php.

Fixture was created by [Travis Bennett](https://twitter.com/tandrewbennett).

* [Requirements](#requirements)
* [Installation](#installation)
* [Overview](#overview)
* [Drivers](#drivers)
* [Setup](#setup)
* [Examples](#examples)
  * [Standard Driver](#standard-driver)
  * [Eloquent Driver](#eloquent-driver)
* [Faking Data](#faking-data)
* [Contributing](#contributing)

## Requirements
* php >= 5.3
* A PDO object instance for database connections.
* Database table primary keys should have a column name of 'id'.
* Database table foreign keys should be composed of the singularized name of the associated table along with an appended '\_id' suffix (e.g blog_id would be the foreign key name for a table named blogs).

## Installation
Fixture is distributed as a composer package, which is how it should be used in your app.

Install the package using Composer.  Edit your project's `composer.json` file to require `codesleeve/fixture`.

```js
  "require": {
    "codesleeve/fixture": "dev-master"
  }
```

## Overview
In order to create good tests for database specific application logic, it's often necessary to seed a test database with dummy data before tests are ran.  This package allows you to achieve this through the use of database fixtures (fixtures are just another way of saying 'test data').  Fixtures can be created using native php array syntax and are not dependendent on any specific relational DBMS.  In a nutshell, this package allows you to turn this:

```php
class fooTest extends PHPUnit_Framework_TestCase
{
	/**
     * A PDO connection instance.
     *
     * @var PDO
     */
	protected $db;

	/**
	 * Initialize our test state.
	 *
	 * @return void
	 */
	public function setUp()
	{
		// create a pdo instance
		if (!$this->db) {
			$this->db = new PDO('sqlite::memory:');
		}

		// populate the users table
		$sql = 'INSERT INTO users (email, password, status, created_at, updated_at) values (?, ?, ?, ?, ?)';
		$sth = $this->prepare($sql);
		$sth->execute(array('john@doe.com', 'abc12345$%^', 1, date('Y-m-d'), date('Y-m-d')));

		$sql = 'INSERT INTO users (email, password, status, created_at, updated_at) values (?, ?, ?, ?, ?)';
		$sth = $this->prepare($sql);
		$sth->execute(array('Jane', 'Doe', 'jane@doe.com', 1, 'abc12345$%^', date('Y-m-d'), date('Y-m-d')));

		$sql = 'INSERT INTO users (email, password, status, created_at, updated_at) values (?, ?, ?, ?, ?)';
		$sth = $this->prepare($sql);
		$sth->execute(array('Jim', 'Doe', 'jim@doe.com', 0, 'abc12345$%^', date('Y-m-d'), date('Y-m-d')));

		// populate the roles table
		$sql = 'INSERT INTO roles (name, created_at, updated_at) values (?, ?, ?)';
		$sth = $this->prepare($sql);
		$sth->execute(array('admin', date('Y-m-d'), date('Y-m-d')));

		$sql = 'INSERT INTO roles (name, created_at, updated_at) values (?, ?, ?)';
		$sth = $this->prepare($sql);
		$sth->execute(array('user', date('Y-m-d'), date('Y-m-d')));

		// populate the roles_users table
		$sql = 'INSERT INTO roles_users (role_id, user_id) values (?, ?)';
		$sth = $this->prepare($sql);
		$sth->execute(array(1, 1));

		$sql = 'INSERT INTO roles_users (role_id, user_id) values (?, ?)';
		$sth = $this->prepare($sql);
		$sth->execute(array(1, 2));

		$sql = 'INSERT INTO roles_users (role_id, user_id) values (?, ?)';
		$sth = $this->prepare($sql);
		$sth->execute(array(2, 3));
	}

	/**
	 * Reset our test state.
	 *
	 * @return void
	 */
	public function tearDown
	{
		$this->db->query("TRUNCATE TABLE users");
		$this->db->query("TRUNCATE TABLE roles");
		$this->db->query("TRUNCATE TABLE roles_users");
	}

	/**
	 * A database integration test of some sort.
	 *
     * @test
	 * @return void
	 */
	public function it_should_be_able_to_do_some_query()
	{
        // Test that some query is working correctly.
	}
}
```

into this:

```php
class fooTest extends PHPUnit_Framework_TestCase
{
	use Codesleeve\Fixture\Fixture;

	/**
     * The fixture instance.
     *
     * @var Fixture
     */
	protected $fixture;

	/**
	 * Initialize our test state.
	 *
	 * @return void
	 */
	public function setUp()
	{
		// set the fixture instance
		$this->fixture = Fixture::getInstance();

		// populate our tables
		$this->fixture->up();
	}

	/**
	 * Reset our test state.
	 *
	 * @return void
	 */
	public function tearDown
	{
		$this->fixture->down();
	}

	/**
	 * A database integration test of some sort.
	 *
     * @test
	 * @return void
	 */
	public function it_should_be_able_to_do_some_query()
	{
        // Test that some query is working correctly.
	}
}
```

## Drivers
Fixture currently supports two drivers:
* Standard Driver - This is the most basic driver avaialble for this package.  It requires no ORM and has no concept of relationships.
* Eloquent Driver - This driver allows full usage of the Eloquent ORM.  When creating fixture data, eloquent relationships can be used in order to easily manage foreign keys among fixture data.

## Setup
In order to use fixture, you're going to first need to initialize it.  A good place to do this is inside your bootstrap file (configured via your phpunit.xml), but you're certainly welcome to do this where it makes the most sense for you:

```php
// Create a pdo instance (this may already exist in your app)
$db = new \PDO('sqlite::memory:');

// Use the pdo instance to create a new driver instance
$driver = new Codesleeve\Fixture\Drivers\Standard($db);

// Create a configuration array.  
$config = array(
	'location' => 'path/to/your/fixtures.php'	// The directory you wish to load fixture files from.
);

// Fixture implements a singleton pattern.
// Get an instance of Fixture and set the config and driver.
$fixture = Fixture::getInstance();
$fixture->setConfig($config);
$fixture->setDriver($driver);

// Alternatively, you could do this in one swoop.
$fixture = Fixture::getInstance($config, $driver);
```

## Examples
For our examples, let's assume that we have the following bleach-themed system:
* Tables:
	* soul_reapers
	* zanpakutos
	* ranks
	* ranks_soul_reapers (columns: integer rank_id, integer soul_reaper_id, integer status).
* Relationships:
	* A soul reaper has one zanpakuto, belongs to many ranks (many to many).
	* A zanpakuto belongs to one soul reaper only.
	* A rank belongs to many soul reapers.

### Standard Driver
#### Step 1 - Fixture setup
Inside your application test folder, create a folder named fixtures.  Next, create a couple of fixture files inside this folder.  Fixture files are written using native php array syntax.  To create one, simply create a new file named after the table that the fixture corresponds to and have it return an array of data.  As an example of this, let's create some fixture data for our 'soul_reapers' table:

in tests/fixtures/soul_reapers.php
```php
return array (
	'Ichigo' => array (
		'first_name' => 'Ichigo',
		'last_name'  => 'Kurosaki'
	),
	'Renji' => array (
		'first_name' => 'Renji',
		'last_name'  => 'Abarai'
	),
	'Genryusai' => array(
		'first_name' => 'Genryusai',
		'last_name'  => 'Yammamoto'
	)
);
```

Here we're simple returning a nested array containing our fixture data.  Notice that there are two fixtures and that they each have a unique name (this is very important as you'll see shortly we can easily reference loaded fixture data from within our tests).  Now, we can't have soul reapers without zanpakutos, so let's seed our zanpakutos table with the following fixture:

in tests/fixtures/zanpakutos.php
```php
return array (
	'Zangetsu' => array (
		'soul_reaper_id' => 'Ichigo',
		'name' => 'Zangetsu',
	),
	'Zabimaru' => array (
		'soul_reaper_id' => 'Renji',
		'name' => 'Zabimaru',
	),
	'Ryujin Jakka' => array(
		'soul_reaper_id' => 'Genryusai',
		'name' => 'Ryujin Jakka',
	)
);
```

Because a zanpakuto must belong to a soul reaper (it's part of their soul after all) we know that our 'zanpakutos' table will contain a column named 'soul_reaper_id'.  In order to tie a zanpakuto to it's owner, we can simply set this foreign key to the name of the corresponding soul reaper it belongs to.  There's no need to worry about specific id's, insertion order, etc.  It's pretty simple.  Moving forward, we've so far been able to easily express our parent/child (1 to 1) relationship between 'soul_reapers' and 'zanpakutos', but what about many to many (join table) relationships?  As an example of how this might work, let's look at two more tables; 'ranks' and 'ranks_soul_reapers'.  Our ranks table fixture will look like this:

in tests/fixtures/ranks.php
```php
return array (
	'Commander' => array (
		'title' => 'Commander'
	),
	'Captain' => array (
		'title' => 'Captain',
	),
	'Lieutenant' => array (
		'title' => 'Lieutenant',
	),
	'Substitute' => array (
		'title' => 'Substitute Shinigami',
	),
);
```

The 'ranks_soul_reapers' (many to many) join table fixture will look like this:

in tests/fixtures/ranks_soul_reapers.php
```php
return array (
	'CommanderYammamoto' => array (
		'soul_reaper_id' => 'Yammamoto',
		'rank_id' 		 => 'Commander'
	),
	'CaptainYammamoto' => array (
		'soul_reaper_id' => 'Yammamoto',
		'rank_id' 		 => 'Captain'
	),
	'LieutenantAbari' => array (
		'soul_reaper_id' => 'Renji',
		'rank_id' 		 => 'Lieutenant'
	),
	'SubstituteKurosaki' => array (
		'soul_reaper_id' => 'Ichigo',
		'rank_id' 		 => 'Substitute'
	)
);
```

Notice that we have both a 'CommanderYammamoto' and a 'CaptainYammamoto' entry inside our ranks_soul_reapers join table; That's because Genryusai Yammamoto was the Captain Commander (he had both the commander role and was also captain level as well) of the Gotei 13.

#### Step 2 - Initialize an instance of the fixture class.
Now that the fixture files have been created, the next step is to create an instance of the fixture library inside of our tests.  Consider the following test (we're using PHPUnit here, but the testing framework doesn't matter; SimpleTest would work just as well):

in tests/exampleTest.php
```php

	use Codesleeve\Fixture\Fixture;
    use Codesleeve\Fixture\Drivers\Standard as StandardDriver;

	/**
     * The fixture instance.
     *
     * @var Fixture
     */
	protected $fixture;

	/**
	 * Initialize our test state.
	 *
	 * @return void
	 */
	public function setUp()
	{
		// set the fixture instance
		$this->fixture = Fixture::getInstance();

		// populate our tables
		$this->fixture->up();
	}

	/**
	 * Reset our test state.
	 *
	 * @return void
	 */
	public function tearDown
	{
		$this->fixture->down();
	}
```

What's going on here?  A few things:
* We're getting an instance of fixture via the getInstance() method (this is a singleton pattern).
* We're invoking the up() method on the fixture object.  This method seeds the database and caches the inserted records as php standard objects on the fixture object.
	* Invoking the up method with no params will seed all fixtures.
	* Invoking the up method with an array of fixture names will seed only those fixtures (e.g $this->fixture->up(array('soul_reapers')) would seed the soul_reapers table only).
* In the tearDown method we're invoking the down() method.  This method will truncate all tables that have had fixture data inserted into them.

As an aded benefit, seeded database records can be accessed (if needed) as php standard objects directly from the fixture object itself:
```php
// Returns 'Kurosaki'
echo $this->fixture->soul_reapers('Ichigo')->last_name;
```

### Eloquent Driver
#### Step 1 - Model setup
Inside your models folder (or wherever you currently store your models at), create both a SoulReaper and a Zanpakuto model:
```php
	class SoulReaper extends Eloquent
	{
		protected $table = 'soul_reapers';

		/**
	     * A soul reaper has one zanpakuto.
	     *
	     * @return hasOne
	     */
	    public function zanpakuto()
	    {
	       return $this->hasOne('Zanpakuto');
	    }

	    /**
	     * A soul reaper belongs to many ranks.
	     *
	     * @return belongsToMany
	     */
	    public function ranks()
	    {
	       return $this->belongsToMany('ranks');
	    }
	}

	class Zanpakuto extends Eloquent
	{
		protected $table = 'zanpakutos';

		/**
	     * A zanpakuto belongs to a Soul Reaper.
	     *
	     * @return belongsTo
	     */
	    public function soulReaper()
	    {
	       return $this->belongsTo('SoulReaper');
	    }
	}
```

#### Step 2 - Fixture setup
Inside your application test folder, create a folder named fixtures.  Next, create a couple of fixture files inside this folder.  Fixture files are written using native php array syntax.  To create one, simply create a new file named after the table that the fixture corresponds to and have it return an array of data.  As we did with our previous example, let's create some fixture data for our soule reapers system:

in tests/fixtures/soul_reapers.php
```php
return array (
	'Ichigo' => array (
		'first_name' => 'Ichigo',
		'last_name'  => 'Kurosaki',
		'ranks' => array('Substitute|active:1')
	),
	'Renji' => array (
		'first_name' => 'Renji',
		'last_name'  => 'Abarai',
		'ranks' => array('Lieutenant|active:1')
	),
	'Genryusai' => array(
		'first_name' => 'Genryusai',
		'last_name'  => 'Yammamoto',
		'ranks' => array('Captain|active:1', 'Commander|active:1')
	)
);
```

in tests/fixtures/zanpakutos.php
```php
return array (
	'Zangetsu' => array (
		'soulReaper' => 'Ichigo',
		'name' => 'Zangetsu',
	),
	'Zabimaru' => array (
		'soulReaper' => 'Renji',
		'name' => 'Zabimaru',
	),
	'Ryujin Jakka' => array(
		'soulReaper' => 'Genryusai',
		'name' => 'Ryujin Jakka',
	)
);
```

in tests/fixtures/ranks.php
```php
return array (
	'Commander' => array (
		'title' => 'Commander'
	),
	'Captain' => array (
		'title' => 'Captain',
	),
	'Lieutenant' => array (
		'title' => 'Lieutenant',
	),
	'Substitute' => array (
		'title' => 'Substitute Shinigami',
	),
);
```

In each of our files, we're simple returning a nested array containing our fixture data.  In this array, we create records (using array syntax) to populate our database tables.

Because we know that a Zanpakto has a 'belongsTo' relationship with a SoulReaper, we can now use this relationship to easily create foreign keys for our fixture data.  There's no need to worry about specific id's, insertion order, etc.  All we need to do is assign the relationship a value (from the belongsTo side) within the fixture and it will be populated automatically.  It's very simple:

 ```php
 // Creates the 'Zangetsu' record in the zanpakutos table and assigns ownership
 // to 'Ichigo' (It populates the 'soul_reaper_id' foreign key for us automatically).
 'Zangetsu' => array (
	'soulReaper' => 'Ichigo',
	'name' => 'Zangetsu',
),
 ```

Many to many (N to N) join table relationships can also be populated. In our running example, soul reapers have a many to many (belongsToMany) relationship with ranks.  In essence, a soul reaper can have many ranks and ranks can belong to many soul reapers (notice that Genryusai has both 'Commander' and 'Captain' ranks; that's because Genryusai Yammamoto was the Captain Commander of the Gotei 13).  To represent this, we simply assign an array to a value that's named after the belongsToMany relationship of the fixture's corresponding model.  In our example, from the soul reapers side (of the belongsToMany relationship), we simple pass an array of 'ranks' (since we defined a belongsToMany relationship named 'ranks' inside our SoulReaper model) we want a soul reaper to have.  Extra columns on the join table can be populated using a '|' delimiter with key/values separated with a ':'.

```php
// This assigns Genryusai the ranks of Captain and Commander and
// sets the 'active' pivot column to 1 for each rank.
'Genryusai' => array(
	'first_name' => 'Genryusai',
	'last_name'  => 'Yammamoto',
	'ranks' => array('Captain|active:1', 'Commander|active:1')
)
```

#### Step 3 - Initialize an instance of the fixture class.
Now that the fixture files have been created, the next step is to create an instance of the fixture library inside of our tests.  Consider the following test (we're using PHPUnit here, but the testing framework doesn't matter; SimpleTest would work just as well):

in tests/exampleTest.php
```php

	use Codesleeve\Fixture\Fixture;
    use Codesleeve\Fixture\Drivers\Eloquent as EloquentDriver;

	/**
     * The fixture instance.
     *
     * @var Fixture
     */
	protected $fixture;

	/**
	 * Initialize our test state.
	 *
	 * @return void
	 */
	public function setUp()
	{
		// set the fixture instance
		$db = new \PDO('sqlite::memory:');
		$driver = new EloquentDriver($db);
		$this->fixture = Fixture::getInstance(array('location' => 'path/to/your/fixtures.php'), $driver);

		// populate our tables
		$this->fixture->up();
	}

	/**
	 * Reset our test state.
	 *
	 * @return void
	 */
	public function tearDown
	{
		$this->fixture->down();
	}
```
## Faking Data
Fixture has built in integration with [Faker](https://github.com/fzaninotto/Faker).  Creating fake fixture data is a breeze:
```php
// We can call Faker through fixture via the 'Fake' method.
// Here, we'll whip up some fake info for Ichigo:
'Ichigo' => array (
	'first_name' => 'Ichigo',
	'last_name'  => 'Kurosaki',
	'ranks' => array('Substitute|active:1'),
	'bio' => Fixture::fake('text'),
	'age' => Fixture::fake('randomNumber', 15, 18),
	'address' => Fixture::fake('address')
),
```

By using fixtures to seed our test database we've gained very precise control over what's in our database at any given time during an integration test.  This in turn allows us to very easily test the pieces of our application that contain database specific logic.

## Key generators
Fixture can support generating primary keys & relation keys for fixture data with different generators. To use a different key generator then the default SHA1KeyGenerator generator. You just need to pass an instance of that generator to the driver. Both the Eloquent & Standard drivers support it.

```php

use Codesleeve\Fixture\KeyGenerators\Crc32KeyGenerator;

$generator = new Crc32KeyGenerator();
//$generator = new SHA1KeyGenerator(10);

new EloquentDriver($db, $generator);

```

## Contributing
Fixture is always open to contributions from the community, however I ask that you please make all pull request to the development branch only.  Let me reiterate this; I will not be accepting pull requests on master.  
