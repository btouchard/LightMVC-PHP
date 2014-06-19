<?php
namespace App\Controller;

use App\Model\News;
use Library\Controller;
use Library\ControllerException;
use Library\Utils\Debug;

/**
 * @property News News
 */
class NewsController extends Controller {

    protected function index() {
        $news = $this->News->find('all');
        if ($this->app->request()->rest()) {
            $this->set('success', $news !== null);
            $this->set('result', $news);
        } else if ($this->app->request()->html()) {
            $this->set('title', 'Index of News');
            $this->set('news', $news);
        }
    }

    protected function show() {
        if (!isset($_GET['id']))
            throw new ControllerException('Identifiant manquant !', 404);
        $news = $this->News->find($_GET['id']);
        if (empty($news))
            throw new ControllerException('Impossible de trouver la news #' . $_GET['id'], 404);
        if ($this->app->request()->rest()) {
            $this->set('success', $news !== null);
            if ($news != null) $this->set('result', $news);
        } else if ($this->app->request()->html()) {
            $this->set('title', 'News: ' . $news->title);
            $this->set('news', $news);
        }
    }

    protected function edit() {
        $this->layout = 'ajax';
        if (!$this->app->request()->html()) $this->app->setError(405, 'Accès réservé au model HTTP !');
        $news = $this->News->find($_GET['id']);
        $this->set('title', 'Edit News');
        $this->set('news', $news);
    }

    protected function save() {
        $news = $this->News->save();
        if ($this->app->request()->rest()) {
            $this->set('success', $news->id > 0);
            $this->set('result', $news);
        } else {
            if ($news->id > 0) {
                $this->app->response()->redirect($this->Html->link($this->module, $news->id));
            }
        }
    }

    protected function delete() {
        $success = $this->News->delete();
        if ($this->app->request()->rest()) {
            $this->set('success', $success);
        } else if ($success) {
            $this->app->response()->redirect($this->Html->link($this->module));
        }
    }
}