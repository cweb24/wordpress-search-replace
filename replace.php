#!/usr/bin/php
<?php
/**
  PHP Version 5
 *
 * *  Search and replace value in wordpress database
 *
  @category Cweb24
  @package  Cweb24
  @author   Nikita Menshutin <nikita@cweb24.com>
  @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
  @link     http://cweb24.com
 * */

/**
 *  Our class goes below
 *
  @category Cweb24
  @package  Cweb24
  @author   Nikita Menshutin <nikita@cweb24.com>
  @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
  @link     http://cweb24.com
 * */
class Dbreplace
{

    /**
     * Read wordpress wp-config.php
     * Connect to the database,
     * call method
     *
     * @param string $s search
     * @param string $r replace
     */
    function __construct($s, $r)
    {
        include_once 'wp-config.php';
        $this->updates = array();
        $this->search  = $s;
        $this->replace = $r;
        $this->conn    = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
        if (!$this->conn) {
            die("Connection failed: ".mysqli_connect_error());
        }
        if (!mysql_select_db(DB_NAME, $this->conn)) {
            echo 'Could not select database';
            exit;
        }
        $this->tables = array(
            'options' => array('option_value'),
            'postmeta' => array('meta_value'),
            'posts' => array(
                'post_excerpt',
                'post_content',
                'guid',
            ),
        );
        $this->prefix = $table_prefix;
        $this->tests  = array('serialize', 'json');
        // connected, now will iterate
        foreach ($this->tables as $table => $fields) {
            foreach ($fields as $field) {
                $this->processtable($table, $field);
            }
        }
        echo "updates:\n ";
        foreach (array_keys($this->updates) as $table) {
            $this->updates[$table]['ids'] = implode(
                '; ', $this->updates[$table]['ids']
            );
        }
        print_r($this->updates);
        echo "\n";
    }

    /**
     * Processing table
     * 1. Select columns having the value which match our search
     * 2. Call processField method
     * 3. If column value is different after that, we call rowUpdate method
     *
     * @param string $table table name
     * @param string $field column to be checked
     *
     * @return void
     */
    public function processTable($table, $field)
    {
        $this->table = $this->prefix.$table;
        $this->field = $field;
        $query       = 'SELECT * FROM `'.
            $this->table.'` WHERE '.
            $this->field.
            ' LIKE "%'.mysql_real_escape_string($this->search).'%"';
        $result      = mysql_query($query, $this->conn);
        while ($row         = mysql_fetch_assoc($result)) {
            $value = $this->processField($row[$field]);
            if ($value != $row[$field]) {
                $row[$field] = $value;
                $this->rowUpdate($row);
            }
        }
    }

    /**
     * Update table row,
     * first column is considered to be ID
     *
     * @param array $row table row
     *
     * @return void
     */
    public function rowUpdate($row)
    {
        $this->updates[$this->table]['total'] ++;
        $id                                            = key($row);
        $this->updates[$this->table]['ids'][$row[$id]] = $row[$id];
        $query                                         = 'UPDATE `'.$this->table.
            '` SET `'.$this->field.'`="'.
            mysql_real_escape_string($row[$this->field]).
            '" WHERE `'.$id.'`="'.$row[$id].'" LIMIT 1';
        mysql_query($query, $this->conn);
    }

    /**
     * Get field from the table row
     * try to process as serialized data
     * then as JSON
     * the search-replace
     *
     * @param string $value to process
     * 
     * @return string
     */
    public function processField($value)
    {
        $this->value = $value;
        $this->processSerialized();
        $this->processJson($this->value);
        return str_replace($this->search, $this->replace, $this->value);
    }

    /**
     * Check the string if it is JSON
     * if true decode, process as array, encode again
     *
     * @param string $string to check and process
     * 
     * @return string
     */
    public function processJson($string)
    {
        if (json_decode($string)) {
            $string = json_encode(
                $this->processArray(
                    json_decode($string, true)
                )
            );
        }
        return $string;
    }

    /**
     * Checking if $this->value is serialized
     * if true call processArray for it and return true
     * else return false
     *
     * @return boolean
     */
    public function processSerialized()
    {
        if (unserialize($this->value)) {
            $this->value = serialize(
                $this->processArray(
                    unserialize($this->value)
                )
            );
            return true;
        }
        return false;
    }

    /**
     * Walking object recursivly if needed
     *
     * @param object $object to walk
     *
     * @return updated object
     */
    public function processObject($object)
    {
        if (!is_object($object)) {
            return $object;
        }
        foreach ($object as $k => $v) {
            $object->$k = $this->processValue($v);
        }
        return $object;
    }

    /**
     * Walking array recursivly if needed
     *
     * @param array $array to walk
     * 
     * @return updated array
     */
    public function processArray($array)
    {
        if (!is_array($array)) {
            return $array;
        }
        foreach ($array as $k => $v) {
            $array[$k] = $this->processValue($v);
        }
        return $array;
    }

    /**
     * Here we check the value
     * if it is object or array and call the respective method
     * if none, we call processJson
     * and finally being sure we've got string or anything 1d
     * we make search-replace
     *
     * @param any $value value to be processed
     * 
     * @return value
     */
    public function processValue($value)
    {
        if (is_object($value)) {
            return $this->processObject($value);
        } elseif (is_array($value)) {
            return $this->processArray($value);
        } else {
            return str_replace(
                $this->search, $this->replace, $this->processJson($value)
            );
        }
    }
}
new Dbreplace($argv[1], $argv[2]);
