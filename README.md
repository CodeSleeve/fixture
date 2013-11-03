#Fixture
A framework agnostic, simple (yet elegant) fixture library for php.

## Requirements
* php >= 5.3.
* A PDO object instance for database connections.
* Database table primary keys should have a column name of 'id'.
* Database table foreign keys should be composed of the singularized name of the associated table along with an appended '_id' suffix (e.g blog_id would be the foreign key name for a table named blogs).

## Installation
Fixture is distributed as a composer package, which is how it should be used in your app.

Install the package using Composer.  Edit your project's `composer.json` file to require `codesleeve/fixture`.

```js
  "require": {
    "codesleeve/fixture": "dev-master"
  }
```

## Quickstart
Inside your application test folder, create a folder named fixtures.  Next, create a couple of fixture files inside this folder.  Fixture files are written using native php array syntax.  To create one, simply create a new file named after the table that the fixture corresponds to and have it return an array of data.  As an example of this, let's create some fixture data for a hypothetical soul_reapers table (bear with me, I'm a huge Bleach fan):

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
		)
	);
```

Here we're simple returning an nested array containing our fixture data.  Notice that there are two fixtures and that they each have a unique name (this is very important as you'll see shortly we can easily reference loaded fixture data from within our tests).  Now, we can't have soul reapers without zanpakutos, so let's assume we've also got a fictional zanpakutos table that we need to seed some data into.  We'll create the following fixture:

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
		)
	);
```