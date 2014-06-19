<?php
namespace App\Model;

use Library\Model;

/**
 * @property mixed User
 * @property mixed Products
 */
class Command extends Model {

    protected $belongsTo = 'User';
    protected $hasAndBelongsToMany = 'Product';
    public $id_user, $created, $updated;

    public function __toString() {
        return 'Command {id: ' . $this->id . ', User: ' . $this->User . ', Products: count(' . count($this->Products) . ')}';
    }

}