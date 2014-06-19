<?php
namespace App\Controller;

use Library\Controller;

/**
 * @property mixed News
 */
class HomeController extends Controller {

    protected function index() {
        $this->set('title', 'Bienvenue sur notre site');
        $news = $this->News->find('last');
        $this->set('news', $news);
    }

}