<?php
/**
 * Author:    Aleksandar Vranešević
 * URL:       http://vavok.net
 */

class Database extends PDO {
    private $error;
    private $sql;
    private $bind;
    private $errorCallbackFunction;
    private $errorMsgFormat;

    public function __construct() {
        $options = array(
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            );

        try {
            parent::__construct('mysql:host=' . DB_HOST . ';dbname=' . DB_DATABASE, DB_USERNAME, DB_PASSWORD, $options);
        } 
        catch (PDOException $e) {
            echo 'Database error: ' . $this->error = $e->getMessage();
        }
    }

    private function debug() {
        if (!empty($this->errorCallbackFunction)) {
            $error = array("Error" => $this->error);
            if (!empty($this->sql))
                $error["SQL Statement"] = $this->sql;
            if (!empty($this->bind))
                $error["Bind Parameters"] = trim(print_r($this->bind, true));

            $backtrace = debug_backtrace();
            if (!empty($backtrace)) {
                foreach($backtrace as $info) {
                    if ($info["file"] != __FILE__)
                        $error["Backtrace"] = $info["file"] . " at line " . $info["line"];
                }
            }

            $msg = "";
            if ($this->errorMsgFormat == 'html') {
                if (!empty($error["Bind Parameters"]))
                    $error["Bind Parameters"] = "<pre>" . $error["Bind Parameters"] . "</pre>";
                $css = trim(file_get_contents(dirname(__FILE__) . "/error.css"));
                $msg .= '<style>' . "\n" . $css . "\n</style>";
                $msg .= "\n" . '<div class="db-error">' . "\n\t<h3>SQL Error</h3>";
                foreach($error as $key => $val)
                $msg .= "\n\t<label>" . $key . ":</label>" . $val;
                $msg .= "\n\t</div>\n</div>";
            } elseif ($this->errorMsgFormat == "text") {
                $msg .= "SQL Error\n" . str_repeat("-", 50);
                foreach($error as $key => $val)
                $msg .= "\n\n$key:\n$val";
            } 

            $func = $this->errorCallbackFunction;
            $func($msg);
        } 
    }

    public function delete($table, $where, $bind = '') {
        $sql = "DELETE FROM " . DB_PREFIX . "{$table} WHERE {$where};";
        $this->run($sql, $bind);
    }

    private function filter($table, $info) {
        $driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
        if ($driver == 'sqlite') {
            $sql = "PRAGMA table_info('" . $table . "');";
            $key = "name";
        } elseif ($driver == 'mysql') {
            $sql = "DESCRIBE " . $table . ";";
            $key = "Field";
        } else {
            $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
            $key = "column_name";
        }

        if (false !== ($list = $this->run($sql))) {
            $fields = array();
            foreach($list as $record)
            $fields[] = $record[$key];
            return array_values(array_intersect($fields, array_keys($info)));
        }
        return array();
    }

    private function cleanup($bind) {
        if (!is_array($bind)) {
            if (!empty($bind))
                $bind = array($bind);
            else
                $bind = array();
        }
        return $bind;
    }

    /**
     * Insert data into database
     *
     * @param string $table
     * @param array $values
     * @return void
     */
    public function insert($table, $values = array()) {
        foreach ($values as $field => $v)
            $ins[] = ':' . $field;

            $ins = implode(',', $ins);
            $fields = implode(',', array_keys($values));
            $sql = "INSERT INTO " . DB_PREFIX . "{$table} ($fields) VALUES ($ins)";

            $sth = $this->prepare($sql);
            foreach ($values as $f => $v) {
                $sth->bindValue(':' . $f, $v);
            }
            $sth->execute();

        // Count number of db queries while debugging
        $this->db_queries();
    }

    public function run($sql, $bind = '') {
        $this->sql = trim($sql);
        $this->bind = $this->cleanup($bind);
        $this->error = "";

        try {
            $pdostmt = $this->prepare($this->sql);
            if ($pdostmt->execute($this->bind) !== false) {
                if (preg_match("/^(" . implode("|", array("select", "describe", "pragma")) . ") /i", $this->sql)) {
                    $results = $pdostmt->fetch(PDO::FETCH_ASSOC);
                    return $results;
                } elseif (preg_match("/^(" . implode("|", array("delete", "insert", "update")) . ") /i", $this->sql)) {
                    return $pdostmt->rowCount();
                } 
            } 
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            $this->debug();
            return false;
        }
    }

    /**
     * Get data from database
     * 
     * @param str $table
     * @param str $where
     * @param str $fields
     * @param str $bind
     * @return array
     */
    public function get_data($table, $where = '', $fields = '*', $bind = '') {
        $sql = 'SELECT ' . $fields . ' FROM ' . DB_PREFIX . $table;
        if (!empty($where))
            $sql .= ' WHERE ' . $where;
        $sql .= ';';

        // Count number of db queries while debugging
        $this->db_queries();

        return $this->run($sql, $bind);
    }

    // count number of rows
    public function count_row($table, $where = '') {
        $sql = 'SELECT count(*) FROM ' . DB_PREFIX . $table;
        if (!empty($where))
            $sql .= ' WHERE ' . $where;
        $sql .= ';';

        $result = $this->query($sql);

        // Count number of db queries while debugging
        $this->db_queries();
    
        return $result->fetch(PDO::FETCH_NUM)[0];
    }

    public function setErrorCallbackFunction($errorCallbackFunction, $errorMsgFormat = "html") { 
        // Variable functions for won't work with language constructs such as echo and print, so these are replaced with print_r.
        if (in_array(strtolower($errorCallbackFunction), array("echo", "print")))
            $errorCallbackFunction = "print_r";

        if (function_exists($errorCallbackFunction)) {
            $this->errorCallbackFunction = $errorCallbackFunction;
            if (!in_array(strtolower($errorMsgFormat), array("html", "text")))
                $errorMsgFormat = "html";
            $this->errorMsgFormat = $errorMsgFormat;
        }
    }

    /*
    // updating one item
    $vavok->go('db')->update('table', 'field', 'value', 'id = 1');
     
    //updating multiple items
    $fields[] = 'name';
    $fields[] = 'description';
     
    $values[] = $_POST['name'];
    $values[] = $_POST['description'];
     
    $vavok->go('db')->update('table', $fields, $values, 'something = "value"');
    */
    public function update($table, $fields, $values, $where = '') {
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
        $this->db_queries();

        //record and print any DB error that may be given
        $error = $prepareUpdate->errorInfo();
        if ($error[1]) { print_r($error); } else { return $prepareUpdate->rowCount(); }
    }

    public function table_exists($table) {
        // Try a select statement against the table
        // Run it in try/catch in case PDO is in ERRMODE_EXCEPTION.
        try {
            $result = $this->query("DESCRIBE " . $table);

            if (!empty($result)) {
                return true;
            }
        } 
        catch (Exception $e) {
            // We got an exception == table not found
            return false;
        } 
        // Result is either boolean FALSE (no table found) or PDOStatement Object (table found)
        return $result !== false;
    }

    public function copy_table($table, $prefix)
    {
        $this->query("CREATE TABLE " . $prefix . $table . " LIKE " . $table);
    }

    /**
     * Count number of db queries while debugging
     *
     * @return void
     */
    public function db_queries()
    {
        if (defined('SITE_STAGE') && SITE_STAGE == 'debug') {
            if (!isset($_SESSION['db_queries'])) $_SESSION['db_queries'] = 0;
            $_SESSION['db_queries'] = $_SESSION['db_queries'] + 1;
        }
    }

    /**
     * Show number of db queries while debugging
     *
     * @return int
     */
    public function show_db_queries()
    {
        $queries = $_SESSION['db_queries'];

        // Reset number of queries in session
        unset($_SESSION['db_queries']);

        return '<p class="site_db_queries">DB queries: ' . $queries . '</p>';
    }
}

?>