<?php
namespace App\Model;

use Library\Model;

class Product extends Model {

    protected $hasAndBelongsToMany = 'Image';
    public $title, $description, $created, $updated, $quantity;

}