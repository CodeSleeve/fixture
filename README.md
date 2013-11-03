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

## Quickstart
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
			'soul_reaper_id' => 'Yammamoto',
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
			'rank_id' 		=> 'Captain'
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

