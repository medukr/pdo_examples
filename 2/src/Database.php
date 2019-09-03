<?php declare(strict_types=1);

namespace DevStart;

use PDO;
use PDOException;

class Database
{
    /**
     * The PDO object.
     *
     * @var PDO
     * */
    private $pdo;

    /**
     * Connected to the database.
     *
     * @var bool
     */
    private $isConnected;

    /**
     * PDO statement object.
     *
     * @var PDOStatement
     */
    private $statement;

    /**
     * The database settings.
     *
     * @var array
     */
    private $settings = [];

    /**
     * The parameters of the SQL query.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * Database constructor.
     * @param array $settings
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;

        $this->connect();
    }

    private function connect()
    {

        $dsn = 'sqlite:';
        $path_to_db = dirname(__FILE__, 2). $this->settings['path_to_db'];


        try {

            $this->pdo = new PDO($dsn . $path_to_db);
            $this->checkDb();

            #Disable emulations and we can now log
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $this->isConnected = true;
        } catch (PDOException $e) {
            print_r( $e->getTrace());
            exit("Error connect: " . $e->getMessage());
        }
    }

    private function checkDb(){
        if (!filesize( dirname(__FILE__, 2). '/db/users.sqlite3')) {
            try {
                $sql = "CREATE TABLE users(
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        user_name TEXT)";

                if (!$this->pdo->exec($sql)) throw new \Exception('CREATE user ERROR');
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }
    }


    public function closeConnection()
    {
        $this->pdo = null;
    }

    /**
     * @param string $query
     * @param array $parameters
     */
    private function init(string $query, array $parameters = [])
    {
        if (!$this->isConnected) $this->connect();

        try {
            #Prepare query
            $this->statement = $this->pdo->prepare($query);

            #Bind parameters
            $this->bind($parameters);

            $this->statement->setFetchMode(PDO::FETCH_CLASS, 'model\User', [1]);

            $this->statement->execute();

        } catch (PDOException $e) {
            exit('Error init: ' . $e->getMessage());
        }

        $this->parameters = [];
    }

    /**
     * @param array $parameters
     * @return void
     */
    private function bind(array $parameters): void
    {
        if (!empty($parameters)) {
            $columns = array_keys($parameters);

            foreach ($columns as $i => &$column) {
                $this->parameters[] = [
                    ':' . $column,
                    $parameters[$column]
                ];
            }


            if (!empty($this->parameters)) {
                foreach ($this->parameters as $value) {

                    if (is_int($value[1])) $type = PDO::PARAM_INT;
                    elseif (is_bool($value[1])) $type = PDO::PARAM_BOOL;
                    elseif (is_null($value[1])) $type = PDO::PARAM_NULL;
                    else $type = PDO::PARAM_STR;

                    $this->statement->bindParam($value[0], $value[1], $type);
                }
            }
        }
    }

    /**
     * @param string $query
     * @param array $parameters
     * @param int $mode
     * @return array|int|null
     */
    public function query(string $query, array $parameters = [], $mode = PDO::FETCH_ASSOC)
    {
        $query = trim(str_replace('\r', ' ', $query));

        $this->init($query, $parameters);

        $rawStatement = explode(' ', preg_replace("/\s+|\t+|\n+/", " ", $query));
        $statement = strtolower($rawStatement[0]);

        try {

            if ($statement === 'select' || $statement === 'show') {
//                return $this->statement->fetchObject('model\User', ['test_parameter']);
                return $this->statement->fetchAll();
//                return $this->statement->fetchAll($mode);
            } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
                return $this->statement->rowCount();
            } else {
                return null;
            }

        } catch (PDOException $e) {
            exit('Error query ' . $e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}