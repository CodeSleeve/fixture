<?php namespace Codesleeve\Fixture\Drivers;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use PDO;

class Eloquent extends BaseDriver implements DriverInterface
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
     * An instance of Laravel's Str class.
     *
     * @var Str
     */
    protected $str;

    /**
     * Constructor method
     *
     * @param  DatabaseManager $db
     * @param  Str $str
     */
    public function __construct(PDO $db, Str $str)
    {
        $this->db = $db;
        $this->str = $str;
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
            $model = $this->generateModelName($tableName);
            $record = new $model;

            foreach ($recordValues as $columnName => $columnValue) {
                $camelKey = camel_case($columnName);

                // If a column name exists as a method on the model, we will just assume
                // it is a relationship and we'll generate the primary key for it and store
                // it as a foreign key on the model.
                if (method_exists($record, $camelKey)) {
                    $this->insertRelatedRecords($recordName, $record, $camelKey, $columnValue);

                    continue;
                }

                $record->$columnName = $columnValue;
            }

            // Generate a hash for this record's primary key.  We'll simply hash the name of the
            // fixture into an integer value so that related fixtures don't have to rely on
            // an auto-incremented primary key when creating foreign keys.
            $primaryKeyName = $record->getKeyName();
            $record->$primaryKeyName = $this->generateKey($recordName);
            $record->save();
            $insertedRecords[$recordName] = $record;
        }

        return $insertedRecords;
    }

    /**
     * Insert related records for a fixture.
     *
     * @param  string $recordName
     * @param  Model $record
     * @param  string $camelKey
     * @param  string $columnValue
     * @return void
     */
    protected function insertRelatedRecords($recordName, Model $record, $camelKey, $columnValue)
    {
        $relation = $record->$camelKey();

        if ($relation instanceof BelongsTo) {
            $this->insertBelongsTo($record, $relation, $columnValue);

            return;
        }

        if ($relation instanceof BelongsToMany) {
            $this->insertBelongsToMany($recordName, $relation, $columnValue);

            return;
        }
    }

    /**
     * Insert a belongsTo foreign key relationship.
     *
     * @param  Model $record
     * @param  Relation $relation
     * @param  int $columnValue
     * @return void
     */
    protected function insertBelongsTo(Model $record, Relation $relation, $columnValue)
    {
        $foreignKeyName = $relation->getForeignKey();
        $foreignKeyValue = $this->generateKey($columnValue);
        $record->$foreignKeyName = $foreignKeyValue;
    }

    /**
     * Insert a belongsToMany foreign key relationship.
     *
     * @param  string recordName
     * @param  Relation $relation
     * @param  int $columnValue
     * @return void
     */
    protected function insertBelongsToMany($recordName, Relation $relation, $columnValue)
    {
        $joinTable = $relation->getTable();
        $this->tables[] = $joinTable;
        $relatedRecords = explode(',', str_replace(', ', ',', $columnValue));

        foreach ($relatedRecords as $relatedRecord) {
            list($fields, $values) = $this->buildBelongsToManyRecord($recordName, $relation, $relatedRecord);
            $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
            $sql = "INSERT INTO $joinTable ($fields) VALUES ($placeholders)";
            $sth = $this->db->prepare($sql);
            $sth->execute($values);
        }
    }

    /**
     * Parse the fixture data for belongsToManyRecord.
     * The current syntax allows for pivot data to be provided
     * via a pipe delimiter with colon separated key values.
     * <code>
     *    'Travis' => [
     *        'first_name'   => 'Travis',
     *        'last_name'    => 'Bennett',
     *        'roles'         => 'endUser|foo:bar, root'
     *    ]
     * </code>
     *
     * @param  string $recordName The name of the relation the fixture is defined on (e.g Travis).
     * @param  Relation $relation The relationship oject (should be of type belongsToMany).
     * @param  string $relatedRecord The related record data (e.g endUser|foo:bar or root).
     * @return array
     */
    protected function buildBelongsToManyRecord($recordName, Relation $relation, $relatedRecord)
    {
        $pivotColumns = explode('|', $relatedRecord);
        $relatedRecordName = array_shift($pivotColumns);

        $foreignKeyPieces = explode('.', $relation->getForeignKey());
        $foreignKeyName = $foreignKeyPieces[1];
        $foreignKeyValue = $this->generateKey($recordName);

        $otherKeyPieces = explode('.', $relation->getOtherKey());
        $otherKeyName = $otherKeyPieces[1];
        $otherKeyValue = $this->generateKey($relatedRecordName);
        
        $fields = "$foreignKeyName, $otherKeyName";
        $values = array($foreignKeyValue, $otherKeyValue);
        
        foreach ($pivotColumns as $pivotColumn) {
            list($columnName, $columnValue) = explode(':', $pivotColumn);
            $fields .= ", $columnName";
            $values[] = $columnValue;
        }

        return array($fields, $values);
    }

    /**
     * Generate the name of table's corresponding model.
     *
     * @param  string $tableName
     * @return string
     */
    protected function generateModelName($tableName)
    {
        return $this->str->singular(str_replace(' ', '', ucwords(str_replace('_', ' ', $tableName))));
    }
}
