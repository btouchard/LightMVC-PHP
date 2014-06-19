<?php
namespace App;

use Library\Application;

class Site extends Application {

    public function __construct() {
        parent::__construct();
        $this->set('title', 'Framework PHP by kOlapsis');
        $this->set('header', 'Mon entête');
        $this->set('footer', 'Mon pied de page');
    }
}