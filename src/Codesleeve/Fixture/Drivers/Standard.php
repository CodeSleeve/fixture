<?php namespace Codesleeve\Fixture\Drivers;

use Codesleeve\Fixture\KeyGenerators\KeyGeneratorInterface;
use PDO;

class Standard extends BaseDriver implements DriverInterface
{
    /**
     * A PDO connection instance.
     *
     * @var PDO
     */
    protected $db;

    /**
     * An array of tables that have had fixture data loaded into them.
     *
     * @var array
     */
    protected $tables = array();

    /**
     * Constructor method
     *
     * @param PDO $db
     * @param KeyGeneratorInterface $keyGenerator
     */
    public function __construct(PDO $db, KeyGeneratorInterface $keyGenerator = null)
    {
        parent::__construct($keyGenerator);
        $this->db = $db;
    }

    /**
     * Build a fixture record using the passed in values.
     *
     * @param  string $tableName
     * @param  array $records
     * @return array
     */
    public function buildRecords($tableName, array $records)
    {
        $insertedRecords = array();
        $this->tables[$tableName] = $tableName;

        foreach ($records as $recordName => $recordValues) {
            // Generate a hash for this record's primary key.  We'll simply hash the name of the
            // fixture into an integer value so that related fixtures don't have to rely on
            // an auto-incremented primary key when creating foreign keys.
            $recordValues = $this->setForeignKeys($recordValues);
            $recordValues = array_merge($recordValues, array('id' => $this->generateKey($recordName)));

            $fields = implode(', ', array_keys($recordValues));
            $values = array_values($recordValues);
            $placeholders = rtrim(str_repeat('?, ', count($recordValues)), ', ');
            $sql = "INSERT INTO $tableName ($fields) VALUES ($placeholders)";

            $sth = $this->db->prepare($sql);
            $sth->execute($values);

            $insertedRecords[$recordName] = (object) $recordValues;
        }

        return $insertedRecords;
    }

    /**
     * Loop through each of the fixture column/values.
     * If a column ends in '_id' we're going to assume it's
     * a foreign key and we'll hash it's values.
     *
     * @param array $values
     * @return array
     */
    protected function setForeignKeys(array $values)
    {
        foreach ($values as $key => &$value) {
            if ($this->endsWith($key, '_id')) {
                $value = $this->generateKey($value);
            }
        }

        return $values;
    }

    /**
     * Determine if a string ends with a set of specified characters.
     *
     * @param  string $haystack
     * @param  string $needle
     * @return boolean
     */
    protected function endsWith($haystack, $needle)
    {
        return $needle === "" || substr($haystack, -strlen($needle)) === $needle;
    }
}
