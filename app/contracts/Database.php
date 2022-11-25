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
     * @param string $table
     * @param string $where
     * @param array $bind use named placeholders
     * @param string $fields
     * @return array|bool
     */
    public function selectData(string $table, string $where = '', array $bind = array(), string $fields = '*'): array|bool;

    /**
     * Count number of rows
     * 
     * @param string $table
     * @param string $where
     * @return integer
     */
    public function countRow(string $table, string $where = ''): int;

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
    public function update(string $table, string|array $fields, string|array $values, string $where = ''): int;

    /**
     * Delete
     * 
     * @param string $table
     * @param string $where
     * @return int
     */
    public function delete(string $table, string $where): int;
}