<?php

/**
 * DB Class - Yet Another Simple PDO Class
 *
 * @author     Author: Leroy Loredo.
 * @git        https://github.com/lele140686/Yet-Another-Simple-PDO-Class
 * @version    1.0
 */
class Db
{
    /**
     * CONST DEBUG Shows or not DEBUG info
     */
    const DEBUG = true;

    /**
     * @var Db instance
     */
    private $instance = NULL;

    /**
     * @var string Db table
     */
    private $table;

    /**
     *   Default Constructor
     *
     *   1. Read configuration file.
     *    2. Creates the parameter array.
     *    3. Instantiate PDO class.
     *    4. Connect to database.
     */
    public function __construct()
    {
        try {
            $parameters = parse_ini_file('config.ini');
        } catch (Exception $e) {
            echo 'Error reading configuration file: ', $e->getMessage(), "\n"; # TODO Check with client how to handle this
            exit;
        }

        // Default PDO attribute options
        $pdo_options = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true
        );
        $this->instance = new PDO('mysql:host=' . $parameters['database_host'] . ';dbname=' . $parameters['database_name'] . ';charset=utf8', $parameters['database_user'], $parameters['database_password'], $pdo_options);
    }

    /**
     * Return PDO instance
     *
     * @return PDO
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * Current working table
     *
     * @var string $table
     *
     * @return Db
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Execute query with given parameters
     *
     * @var string $query
     * @var array $parameters
     *
     * @return Statement|Error
     */
    private function query($query, $parameters = array())
    {
        try {
            $sth = $this->instance->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
            $sth->execute($parameters);

            return $sth;
        } catch (Exception $e) {
            if (self::DEBUG)
                echo "Failed: " . $e->getMessage();
            else
                header("HTTP/1.0 404 Not Found");
            exit;
        }
    }

    /**
     * Build WHERE for future query
     *
     * @var array $where
     *
     * @return string
     */
    private function where($where = array())
    {
        $count = count($where);

        // Build where if needed
        $where_query = '';
        $c = 0;
        foreach ($where as $column => $value) {
            if ($c == 0)
                $where_query .= ' WHERE ';

            $where_query .= "$column = ?";

            if (++$c < $count)
                $where_query .= ' AND ';
        }

        return $where_query;
    }

    /**
     * Get a list of elements (Default: Objects)
     *
     * @var array $where
     * @var CONST $param
     * @var int $limit
     * @var array $columns
     *
     * @return array|StdClass
     */
    public function findAll($where = array(), $param = PDO::FETCH_OBJ, $limit = 0, $columns = array())
    {
        $where_query = $this->where($where);

        $query = 'SELECT ' . (!empty($columns) ? implode(',', $columns) : '*') . ' FROM ' . $this->table . $where_query . ($limit ? " LIMIT $limit" : '');

        if ($limit == 1)
            return $this->query($query, array_values($where))->fetchObject();
        return $this->query($query, array_values($where))->fetchAll($param);
    }

    /**
     * Find one object
     *
     * @var array $where
     *
     * @return StdClass
     */
    public function findOne($where = array())
    {
        return $this->findAll($where, null, 1);
    }

    /**
     * Update a list or single object
     *
     * @var array $data
     * @var array $where
     *
     * @return mixed
     */
    public function update($data, $where = array())
    {
        $where_query = $this->where($where);

        $columns = array();
        foreach ($data as $key => $value) {
            $columns[] = "$key = ?";
        }
        $bindings = array_values($data);

        $query = 'UPDATE ' . $this->table . ' SET ' . implode(',', $columns) . $where_query;

        return $this->query($query, array_merge($bindings, array_values($where)));
    }

    /**
     * Insert new object or replace existing one
     * For replace remember to set INDEXES
     *
     * @var array $data
     * @var bool $replace
     *
     * @return integer
     */
    public function insert($data, $replace = false)
    {
        $string = '';
        foreach ($data as $key => $value) {
            $string .= '?,';
        }
        $string = trim($string, ',');

        $query = 'INSERT INTO ';
        if ($replace)
            $query = 'REPLACE INTO ';
        $query = $query . $this->table . '(' . implode(',', array_keys($data)) . ') VALUES (' . $string . ')';

        $result = $this->query($query, array_values($data));

        if ($result)
            return $this->instance->lastInsertId();
        return 0;
    }

    /**
     * Delete single object or object list
     *
     * @var array $where
     *
     * @return bool
     */
    public function delete($where = array())
    {
        $where_query = $this->where($where);

        $query = 'DELETE FROM ' . $this->table . $where_query;

        return $this->query($query, array_values($where));
    }

    /**
     * Count elements
     *
     * @var array $where
     *
     * @return integer
     */
    public function count($where = array())
    {
        $where_query = $this->where($where);

        $query = 'SELECT COUNT(*) FROM ' . $this->table . $where_query;

        return $this->query($query, array_values($where))->fetchColumn();
    }
}
