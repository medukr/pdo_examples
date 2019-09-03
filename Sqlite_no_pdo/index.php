<?php

spl_autoload_register(function ($classname){
    $path_to_class = str_replace('\\', DIRECTORY_SEPARATOR, $classname);

    require_once __DIR__ . DIRECTORY_SEPARATOR . $path_to_class . ".php";
});

$db = new SQLite3DB();

