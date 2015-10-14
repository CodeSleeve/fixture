<?php namespace Codesleeve\Fixture\Drivers;

interface DriverInterface
{

    /**
     * Build a fixture record using the passed in values.
     *
     * @param  string $tableName
     * @param  array $records
     * @param  array $config
     * @return array
     */
    public function buildRecords($tableName, array $records, array $config);

    /**
     * Truncate a table.
     *
     * @return void
     */
    public function truncate();
}
