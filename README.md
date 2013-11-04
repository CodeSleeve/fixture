#Fixture
A framework agnostic, simple (yet elegant) fixture library for php.

## Requirements
* php >= 5.3.
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
In order to create good tests for database specific application logic, it's often necessary to seed a test database with dummy data before tests are ran.  This package allows you to achieve this through the use of database fixtures (fixtures are just another way of saying 'test data').  Fixtures can be created using native php array syntax and are not dependendent on any specific relational DBMS.  You'll typically create one fixture for each table in your database that you wish to seed. 

## Example
### Step 1 - Fixture setup
Inside your application test folder, create a folder named fixtures.  Next, create a couple of fixture files inside this folder.  Fixture files are written using native php array syntax.  To create one, simply create a new file named after the table that the fixture corresponds to and have it return an array of data.  As an example of this, let's create some fixture data for a hypothetical 'soul_reapers' table (bear with me, I'm a huge Bleach fan):

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

Here we're simple returning a nested array containing our fixture data.  Notice that there are two fixtures and that they each have a unique name (this is very important as you'll see shortly we can easily reference loaded fixture data from within our tests).  Now, we can't have soul reapers without zanpakutos, so let's assume we've also got a fictional 'zanpakutos' table that we need to seed some data into.  We'll create the following fixture:

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

Because a zanpakuto must belong to a soul reaper (it's part of their soul after all) we know that our 'zanpakutos' table will contain a column named 'soul_reaper_id'.  In order to tie a zanpakuto to it's owner, we can simply set this foreign key to the name of the corresponding soul reaper it belongs to.  There's no need to worry about specific id's, insertion order, etc.  It's pretty simple.  Moving forward, we've so far been able to easily express our parent/child (1 to 1) relationship between 'soul_reapers' and 'zanpakutos', but what about many to many (join table) relationships?  As an example of how this might work, let's now assume that we also have two more tables; 'ranks' and 'ranks_soul_reapers'.  Our ranks table fixture will look like this:

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

The 'ranks_soul_reapers' join table fixture will look like this:

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

### Step 2 - Initialize an instance of the fixture class.
Now that the fixture files have been created, the next step is to create an instance of the fixture library inside of our unit tests.  Consider the following test (we're using PHPUnit here, but the testing framework doesn't matter; SimpleTest would work just as well):

in tests/exampleTest.php
```php
<?php

	use Codesleeve\Fixture\Fixture;
	use Codesleeve\Fixture\Repositories\StandardRepository;

	class ExampleTest extends PHPUnit_Framework_TestCase {

		protected $fixture;
		protected $repository;

		public function setUp()
		{
			if (!$this->repository) 
			{
				$pdo = new PDO('mysql:dbname=testdb;host=127.0.0.1', 'dbuser', 'dbpass');
				$this->repository =  new StandardRepository($pdo);
			}

			$this->fixture = Fixture::getInstance();
			$this->fixture->setRepository($this->repository);
			$this->fixture->setConfig(array('location' => ''));
			$this->fixture->up();
		}

		public function tearDown()
		{
			$this->fixture->down();
		}
	}
?>
```

What's going on here?  A few things:
* We're creating an instance of 'Codesleeve\Fixture\Repositories\StandardRepository' and caching it as a property on the test class.
	* This is the most basic repository avaialble for this package.  It requires no ORM and has no concept of relationships.
	* In order to create a new repository we first need to instantiate a PDO database connection object and pass it as a parameter to the StandardRepository constructor.
* We're creating a new instance of fixture via the getInstance() method (this is a singleton pattern).
* We're injecting the stnadardRepository object into the fixture instance via the setRepository() method.
* We're injecting in a configuration array with a location parameter that contains the file system location of the folder we want to load our fixtures from.
* We're invoking the up() method on the fixture object.  This method seeds the database and caches the inserted records as php standard objects on the fixture object.
	* Invoking the up method with no params will seed all fixtures.
	* Invoking the up method with an array of fixture names will seed only those fixtures (e.g $this->fixture->up(['soul_reapers']) would seed the soul_reapers table only).
* In the tearDown method we're invoking the down() method.  This method will truncate all tables that have had fixture data inserted into them.

As an aded benefit, seeded database records can be accessed (if needed) as objects directly from the fixture object itself:
```php
// Returns 'Kurosaki'
echo $this->fixture->soul_reapers('Ichigo')->last_name;
```

By using fixtures to seed our test database we've gained very precise control over what's in our database at any given time during a test.  This in turn allows us to very easily test the pieces of our application that contain database specific logic.  