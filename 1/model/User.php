<?php

namespace model;

class User
{
    public $user_name;
    public $id;
    private $parameter;

    public function __construct($parameter)
    {
        $this->parameter = $parameter;
    }
}