<?php
namespace Gurkan\Helper;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    /**
     * @var PDO
     */
    private static $connection;


    /**
     * Veritabanı bağlantınısı sağlar.
     */
    public static function connect($config)
    {
        try {
            static::$connection = new PDO('mysql:host='. $config['host'] .';dbname='. $config['database'] .';charset='. $config['charset'], $config['username'], $config['password']);

        } catch (PDOException $e){
            echo $e->getMessage();
        }
    }


    /**
     * Veritabanı sorgusu yapar.
     *
     * @param string $sql
     * @param array $parameters
     *
     * @example DB::query('SELECT * FROM table WHERE id = :id, name = :name', array(':id' => 1, ':name' => 'Name');
     * @return bool|PDOStatement
     */
    public static function query($sql, $parameters = array())
    {
        $query = static::$connection->prepare($sql);
        $query->setFetchMode(PDO::FETCH_OBJ);

        $success = $query->execute($parameters);

        if ($success === true) {
            return $query;
        }

        return false;
    }


    /**
     * Sorgu yapıp tüm kayıtları döndürür.
     *
     * @param string $sql
     * @param array $parameters
     * @return array
     */
    public static function fetchAll($sql, $parameters = array())
    {
        $query = static::query($sql, $parameters);

        return $query->fetchAll();
    }


    /**
     * Sorgu yapıp ilk kaydı döndürür.
     *
     * @param string $sql
     * @param array $parameters
     * @return mixed
     */
    public static function fetch($sql, $parameters = array())
    {
        $query = static::query($sql, $parameters);

        return $query->fetch();
    }


    /**
     * Kolay select sorgusu.
     *
     * @param string $table
     * @param mixed $value
     * @param string $column
     * @return mixed
     */
    public static function find($table, $value, $column = 'id')
    {
        return static::fetch('SELECT * FROM '. $table .' WHERE '. $column .' = :'. $column .' LIMIT 1', array(":$column" => $value));
    }


    /**
     * Kolay count sorgusu.
     *
     * @param string $table
     * @param array $parameters
     * @return string
     */
    public static function count($table, $parameters = array())
    {
        $where = '';

        if (count($parameters)> 0) {
            $where = ' WHERE '. static::parameterForSets($parameters);
        }

        $query = static::query('SELECT count(*) as aggregate FROM '. $table . $where, $parameters);

        return $query->fetchColumn();
    }

    /**
     * Kolay insert sorgusu.
     *
     * @param string $table
     * @param array $parameters
     * @return bool|string
     */
    public static function insert($table, $parameters = array())
    {
        $query = static::query('INSERT INTO '. $table .' SET '. static::parameterForSets($parameters), $parameters);

        if ($query->rowCount() > 0) {
            return static::$connection->lastInsertId();
        }

        return false;
    }


    /**
     * Kolay update sorgusu.
     *
     * @param string $table
     * @param array $parameters
     * @return int
     */
    public static function update($table, $parameters = array())
    {
        $query = static::query('UPDATE '. $table .' SET '. static::parameterForSets($parameters), $parameters);

        return $query->rowCount();
    }

    /**
     * Kolay delete sorgusu.
     *
     * @param string $table
     * @param array $parameters
     * @return int
     */
    public static function delete($table, $parameters = array())
    {
        $where = '';

        if (count($parameters)> 0) {
            $where = ' WHERE '. static::parameterForSets($parameters);
        }

        $query = static::query('UPDATE FROM '. $table . $where, $parameters);

        return $query->rowCount();
    }


    /**
     * Parametreleri insert ve update için hazırlar.
     *
     * @param array $parameters
     * @return string
     */
    private static function parameterForSets(array $parameters)
    {
        $sets = array();

        foreach ($parameters as $key => $value) {
            $sets[] = ltrim($key, ':') .' = '. $key;
        }

        return implode(',', $sets);
    }
}
