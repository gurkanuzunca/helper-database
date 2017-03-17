<?php
namespace Gurkan\Helper;

use PDO;
use PDOException;

/**
 * Class Database
 * Sınıf özellikleri için beklentiye girmemek lazım.
 * Şipşak, basit işlemler ve denemeler için kullanılabilir.
 *
 * @package Gurkan\Helper
 */
class Database
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * Database constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->connect($config);
    }

    /**
     * Veritabanı bağlantınısı sağlar.
     *
     * @param array $config ['host' => 'localhost', 'database' => 'db', 'charset' => 'utf8', 'username' => 'user', 'password' => 'pass']
     * @return void
     */
    public function connect(array $config)
    {
        try {
            $this->connection = new PDO('mysql:host='. $config['host'] .';dbname='. $config['database'] .';charset='. $config['charset'], $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
            ]);
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
     * @example Database::query('SELECT * FROM table WHERE id = :id, name = :name', [':id' => 1, ':name' => 'Name'];
     * @return bool|\PDOStatement
     */
    public function query($sql, $parameters = array())
    {
        $query = $this->connection->prepare($sql);
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
    public function fetchAll($sql, $parameters = array())
    {
        $query = $this->query($sql, $parameters);

        return $query->fetchAll();
    }

    /**
     * Sorgu yapıp ilk kaydı döndürür.
     *
     * @param string $sql
     * @param array $parameters
     * @return mixed
     */
    public function fetch($sql, $parameters = array())
    {
        $query = $this->query($sql, $parameters);

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
    public function find($table, $value, $column = 'id')
    {
        return $this->fetch('SELECT * FROM '. $table .' WHERE '. $column .' = :'. $column .' LIMIT 1', [":$column" => $value]);
    }

    /**
     * Kolay count sorgusu.
     *
     * @param string $table
     * @param array $parameters
     * @return string
     */
    public function count($table, $parameters = array())
    {
        $where = '';

        if (count($parameters)> 0) {
            $where = ' WHERE '. $this->parameterForSets($parameters);
        }

        $query = $this->query('SELECT count(*) as aggregate FROM '. $table . $where, $parameters);

        return $query->fetchColumn();
    }

    /**
     * Kolay insert sorgusu.
     *
     * @param string $table
     * @param array $parameters
     * @return bool|string
     */
    public function insert($table, $parameters = array())
    {
        $query = $this->query('INSERT INTO '. $table .' SET '. $this->parameterForSets($parameters), $parameters);

        if ($query->rowCount() > 0) {
            return $this->connection->lastInsertId();
        }

        return false;
    }

    /**
     * @param $table
     * @param array $data
     *
     * @example Database::insertWithArray('table', ['id' => 1, 'name' => 'Name'];
     *
     * @return bool|string
     */
    public function insertWithArray($table, $data = array())
    {
        $parameters = array();

        foreach ($data as $key => $value)
        {
            $parameters[':' . $key] = $value;
        }

        return $this->insert($table, $parameters);
    }

    /**
     * Kolay update sorgusu.
     *
     * @param string $table
     * @param array $parameters
     * @return int
     */
    public function update($table, $parameters = array())
    {
        $query = $this->query('UPDATE '. $table .' SET '. $this->parameterForSets($parameters), $parameters);

        return $query->rowCount();
    }
    
    /**
     * @param $table
     * @param array $data
     *
     * @example Database::updateWithArray('table', ['id' => 1, 'name' => 'Name'];
     *
     * @return bool|string
     */
    public function updateWithArray($table, $data = array())
    {
        $parameters = array();

        foreach ($data as $key => $value)
        {
            $parameters[':' . $key] = $value;
        }

        return $this->update($table, $parameters);
    }

    /**
     * Kolay delete sorgusu.
     *
     * @param string $table
     * @param array $parameters
     * @return int
     */
    public function delete($table, $parameters = array())
    {
        $where = '';

        if (count($parameters)> 0) {
            $where = ' WHERE '. $this->parameterForSets($parameters);
        }

        $query = $this->query('UPDATE FROM '. $table . $where, $parameters);

        return $query->rowCount();
    }

    /**
     * Parametreleri insert ve update için hazırlar.
     *
     * @param array $parameters
     * @return string
     */
    private function parameterForSets(array $parameters)
    {
        $sets = [];

        foreach ($parameters as $key => $value) {
            $sets[] = ltrim($key, ':') .' = '. $key;
        }

        return implode(',', $sets);
    }

    /**
     * PDO gereksinimleri için PDO nesnesini döndürür.
     *
     * @return PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
