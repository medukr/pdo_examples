<?php
/**
 * Created by andrii
 * Date: 03.09.19
 * Time: 15:36
 */

class SQLite3DB
{
    const DB_NAME = "news.db";
    const ERR_PROPERTY_NAME = 'Wrong property name';

    private $_db;

    public function __construct()
    {
        $this->_db = new \SQLite3( self::DB_NAME);
        if (!filesize( self::DB_NAME)) {
            try {
                $sql = "CREATE TABLE msgs(
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        title TEXT,
                        category INTEGER,
                        description TEXT,
                        source TEXT,
                        datetime INTEGER)";
                if (!$this->_db->exec($sql)) throw new \Exception('CREATE msgs ERROR');

                $sql = "CREATE TABLE category(
                        id INTEGER,
                        name TEXT)";
                if ($this->_db->exec($sql)) throw new \Exception('CREATE category ERROR');

                $sql = "INSERT INTO category(id, name)
                        SELECT 1 as id, 'Политика' as name
                        UNION SELECT 2 as id, 'Культура' as name
                        UNION SELECT 3 as id, 'Спорт' as name";
                if ($this->_db->exec($sql)) throw new \Exception('INSERT category ERROR');
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
    }

    public function __destruct()
    {
        unset($this->_db);
    }

    public function __get($name)
    {
        if ($name == 'db')
            return $this->_db;
        throw new \Exception(self::ERR_PROPERTY_NAME);
    }

    public function __set($name, $value)
    {
        throw new \Exception(self::ERR_PROPERTY_NAME);
    }

    public function saveNews($title, $category, $description, $source)
    {
        $dt = time();

        $sql = "INSERT INTO  `msgs` (`title`, `category`, `description`, `source`, `datetime`) 
                VALUES ('$title', $category, '$description', '$source', $dt)";

        return $this->_db->exec($sql);
    }

    public function db2Arr($data)
    {
        $arr = [];
        while ($row = $data->fetchArray(SQLITE3_ASSOC))
            $arr[] = $row;
        return $arr;
    }

    public function getNews()
    {
        $sql = "SELECT `msgs`.`id` as `id`, `title`, `category`.`name` as `category`, `description`, `source`, `datetime` FROM `msgs`, `category` WHERE category.id = msgs.category ORDER BY msgs.id DESC";

        $items = $this->_db->query($sql);
        if (!$items) return false;

        return $this->db2Arr($items);
    }

    public function deleteNews($id)
    {
        $sql = "DELETE FROM msgs WHERE id=$id";
        return $this->_db->exec($sql);
    }

    public function escape($data)
    {
        return $this->_db->escapeString(trim(strip_tags($data)));
    }
}