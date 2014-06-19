<?php
namespace App\Model;

use Library\Model;

class User extends Model {

    //protected $hasMany = 'Command';
    //public $login, $password, $token, $expire, $created, $updated;

    public function __toString() {
        $vls[] = 'id: ' . $this->id;
        $vls[] = 'login: ' . $this->login;
        //if (isset($this->Commands)) $vls[] = 'Commands: count(' . count($this->Commands) . ')';
        return 'User {' . implode(', ', $vls) . '}';
    }

}