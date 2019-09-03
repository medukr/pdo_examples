<?php

use DevStart\Database;

require_once __DIR__ . '/vendor/autoload.php';

$config = require_once 'config.php';

$db = new Database($config);

$queryToDB = $db->query("select * from users where id > 3");

$db->closeConnection();

print_r($queryToDB);