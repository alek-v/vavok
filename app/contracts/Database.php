<?php

namespace App\Contracts;

interface Database {
    /**
     * Insert data into database
     *
     * @param string $table
     * @param array $values
     * @return void
     */
    public function insert(string $table, array $values = array()): void;

    /**
     * Get data from the database
     * 
     * @param str $table
     * @param str $where
     * @param str $fields
     * @return array|bool
     */
    public function getData($table, $where = '', $fields = '*'): array|bool;

    /**
     * Count number of rows
     * 
     * @param string $table
     * @param string $where
     * @return integer
     */
    public function countRow($table, $where = ''): int;

    /**
     * Update
     * 
     * Updating one item
     * update('table', 'field', 'value', 'id = 1');
     * 
     * updating multiple items
     * update('table', $fields, $values, 'something = "value"');
     * @param string $table
     * @param string|array $fields
     * @param string|array $values
     * @param string $where
     */
    public function update($table, $fields, $values, $where = ''): int;

    /**
     * Delete
     * 
     * @param string $table
     * @param string $where
     * @return int
     */
    public function delete($table, $where): int;
}