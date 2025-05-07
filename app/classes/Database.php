<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       http://vavok.net
 */

namespace App\Classes;

use PDO;
use PDOException;
use App\Contracts\Database as DBInterface;

class Database extends PDO implements DBInterface {
    protected static ?Database $connection = null;
    private string $error;

    public function __construct()
    {
        // PDO options
        $options = array(
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            );

        // Make a connection
        try {
            self::$connection = parent::__construct('mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE, DB_USERNAME, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            echo 'Database error: ' . $this->error = $e->getMessage();
            exit;
        }
    }

    /**
     * Database instance
     *
     * @return object
     */
    public static function instance(): object
    {
        if (self::$connection === null) {
            self::$connection = new self;

            // Redirect to installation page if database is empty
            if (!self::$connection->tableExists(DB_PREFIX . 'settings') && !strpos($_SERVER['REQUEST_URI'], 'install')) {
                header("Location: /install");
                exit;
            }
        }

        return self::$connection;
    }

    /**
     * Insert data into the database
     *
     * @param string $table
     * @param array $values
     * @return void
     */
    public function insert(string $table, array $values = array()): void
    {
        foreach ($values as $field => $v) {
            $ins[] = ':' . $field;
        }

        $ins = implode(',', $ins);
        $fields = implode(',', array_keys($values));
        $sql = "INSERT INTO " . DB_PREFIX . "{$table} ($fields) VALUES ($ins)";

        $sth = $this->prepare($sql);
        foreach ($values as $f => $v) {
            $sth->bindValue(':' . $f, $v);
        }
        $sth->execute();

        // Count number of db queries while debugging
        $this->dbQueries();
    }

    /**
     * Get data from the database
     * 
     * @param string $table
     * @param string $where
     * @param array $bind use named placeholders
     * @param string $fields
     * @return array|bool
     */
    public function selectData(string $table, string $where = '', array $bind = array(), string $fields = '*'): array|bool
    {
        $sql = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . $table;

        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        
        $sql .= ';';

        // Count number of db queries while debugging
        if (SITE_STAGE == 'development') {
            $this->dbQueries();  
        }

        $statement = $this->prepare($sql);
        $statement->execute($bind);

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Count number of rows
     * 
     * @param string $table
     * @param string $where
     * @return integer
     */
    public function countRow(string $table, string $where = ''): int
    {
        $sql = 'SELECT count(*) FROM ' . DB_PREFIX . $table;
        if (!empty($where))
            $sql .= ' WHERE ' . $where;
        $sql .= ';';

        $result = $this->query($sql);

        // Count number of db queries while debugging
        $this->dbQueries();
    
        return $result->fetch(PDO::FETCH_NUM)[0];
    }

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
    public function update(string $table, string|array $fields, string|array $values, string $where = ''): int
    {
        if (!empty($where)) $where = ' WHERE ' . $where;

        //build the field to value correlation
        $buildSQL = '';
        if (is_array($fields)) {
            //loop through all the fields and assign them to the correlating $values
            foreach($fields as $key => $field) :
            if ($key == 0) {
            //first item
            $buildSQL .= $field . ' = ?';
            } else {
            //every other item follows with a ","
            $buildSQL .= ', '.$field.' = ?';
            }
            endforeach;

        } else {
            //we are only updating one field
            $buildSQL .= $fields.' = :value';
        }

        $prepareUpdate = $this->prepare('UPDATE ' . DB_PREFIX . $table . ' SET ' . $buildSQL . $where);

        //execute the update for one or many values
        if (is_array($values)) {
            $prepareUpdate->execute($values);
        } else {
            $prepareUpdate->execute(array(':value' => $values));
        }

        // Count number of db queries while debugging
        $this->dbQueries();

        // Record and print any DB error that may be given
        $error = $prepareUpdate->errorInfo();
        if ($error[1]) print_r($error); else return $prepareUpdate->rowCount();
    }

    /**
     * Delete
     * 
     * @param string $table
     * @param string $where
     * @return int
     */
    public function delete(string $table, string $where): int
    {
        $sql = "DELETE FROM " . DB_PREFIX . "{$table} WHERE {$where};";

        $pdostmt = $this->prepare($sql);
        $pdostmt->execute();

        return $pdostmt->rowCount();
    }

    /**
     * Check if table exists
     *
     * @param string $table
     * @return boolean
     */
    public function tableExists(string $table): bool
    {
        $statement = $this->prepare("SHOW TABLES LIKE '{$table}'");
        $statement->execute();
        if ($statement->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Check if column in the table exists
     *
     * @param string $table
     * @param string $column
     * @return boolean
     */
    public function columnExists(string $table, string $column): bool
    {
        $statement = $this->prepare("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        $statement->execute();
        if ($statement->rowCount() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Count number of db queries while debugging
     *
     * @return void
     */
    private function dbQueries(): void
    {
        if (defined('SITE_STAGE') && SITE_STAGE == 'debug') {
            if (!isset($_SESSION['db_queries'])) $_SESSION['db_queries'] = 0;
            $_SESSION['db_queries'] = $_SESSION['db_queries'] + 1;
        }
    }

    /**
     * Show number of db queries while debugging
     *
     * @return string
     */
    public function showDbQueries(): string
    {
        $queries = $_SESSION['db_queries'];

        // Reset number of queries in session
        unset($_SESSION['db_queries']);

        return '<p class="site_db_queries">DB queries: ' . $queries . '</p>';
    }

    /**
     * Select page with multilingual options
     * First row will be our page, other row(s) will be localization options
     * 
     * @param string $slug
     * @return array|bool
     */
    public function getMultilangPage($slug)
    {
        // SQL query with placeholders to prevent SQL injection
        $sql = "
            (
                SELECT p1.id, p1.page_title, p1.slug_group, p1.localization, p1.slug, p1.content, p1.head_tags, p1.date_created, p1.date_updated, p1.created_by, p1.updated_by, p1.head_tags, p1.published_status, p1.date_published, p1.type, p1.views, p1.default_img, p1.thumbnail, 0 AS order_priority
                FROM pages p1
                WHERE p1.slug = :slug
            )
            UNION ALL
            (
                SELECT NULL AS id, NULL AS page_title, NULL AS slug_group, p2.localization, p2.slug, NULL AS content, NULL AS head_tags, NULL AS date_created, NULL AS date_updated, NULL AS created_by, NULL AS updated_by, NULL AS head_tags, NULL AS published_status, NULL AS date_published, p2.type,NULL AS views, NULL AS default_img, NULL AS thumbnail, 1 AS order_priority
                FROM pages p2
                WHERE p2.slug_group = (SELECT slug_group FROM pages WHERE slug = :slug)
                AND p2.slug != :slug
            )
            ORDER BY order_priority, slug;
        ";
    
        try {
            // Prepare the SQL statement
            $stmt = $this->prepare($sql);
    
            // Bind the parameter to the placeholder
            $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    
            // Execute the query
            $stmt->execute();
    
            // Fetch all results
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Return fetched data
            return $result;
        } catch (PDOException $e) {
            // Handle and log the error
            error_log("Error in getMultilangPage: " . $e->getMessage());
            return false; // Return false in case of failure
        }
    }
}