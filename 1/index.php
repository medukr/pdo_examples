<?php

use DevStart\Database;

require_once __DIR__ . '/vendor/autoload.php';

$config = require_once 'config.php';

$db = new Database($config['db']);

$queryToDB = $db->query("SELECT * FROM `users` WHERE `id` > :id", ['id' => 1]);

print_r($queryToDB);