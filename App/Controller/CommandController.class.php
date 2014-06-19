<?php
namespace App\Controller;

use App\Model\Command;
use Library\Controller;

/**
 * @property Command Command
 */
class CommandController extends Controller {

    protected function index() {
        $commands = $this->Command->find('all');
        if ($this->app->request()->rest()) {
            $this->set('success', $commands !== null);
            $this->set('result', $commands);
        } else if ($this->app->request()->html()) {
            $this->set('title', 'Index of News');
            $this->set('commands', $commands);
        }
    }

    protected function show() {
        $command = $this->Command->find($_GET['id']);
        if ($this->app->request()->rest()) {
            $this->set('success', $command !== null);
            if ($command != null) $this->set('result', $command);
        } else if ($this->app->request()->html()) {
            $this->set('title', 'Commande #' . $command->id);
            $this->set('command', $command);
        }
    }

}