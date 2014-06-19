<?php
namespace App\Controller;

use App\Managers\Html;
use App\Model\User;
use Library\Controller;
use Library\Utils\DB;
use Library\Utils\Debug;

/**
 * @property User User
 * @property Html Html
 */
class UserController extends Controller {

    public static function logged() {
        return isset($_SESSION['id_user']) && $_SESSION['id_user'] > 0;
    }

    protected function index() {
        // TODO: Il va falloir gérer l'authentification
        if (UserController::logged()) {
            $user = $this->User->find($_SESSION['id_user']);
            if ($this->app->request()->rest()) {
                $this->set('success', $user !== null);
                $this->set('result', $user);
            } else if ($this->app->request()->html()) {
                $this->set('title', 'Index of User');
                $this->set('user', $user);
            }
        } else {
            if ($this->app->request()->rest()) $this->app->setError(401, 'Veuillez vous identifier !');
            else if ($this->app->request()->html()) $this->app->response()->redirect($this->Html->link('User', 'login'));
        }
    }

    protected function show() {
        $this->index();
    }

    protected function login() {
        if (UserController::logged()) {
            if ($this->app->request()->rest()) {
                $this->set('success', false);
                $this->set('error', 'Vous êtes déjà identifié');
            } else $this->app->response()->redirect($this->Html->link('User'));
        } else if (count($_POST) > 0) {
            if (isset($_POST['login']) && !empty($_POST['login']) && isset($_POST['password']) && !empty($_POST['password'])) {
                $params = array( 'SELECT' => array('id', 'name', 'token', 'expire'), 'WHERE' => array('login' => $_POST['login'], 'password' => md5($_POST['password'])), 'LIMIT' => '0, 1' );
                $user = $this->User->find($params);
                $success = (bool) !is_null($user) && $user->id > 0;
                if ($success) $_SESSION['id_user'] = $user->id;
                if ($this->app->request()->rest()) $this->set('success', $success);
                else if ($this->app->request()->html()) $this->app->response()->redirect($this->Html->link('User'));
            } else {
                $this->set('error', 'Login or password is empty !');
            }
        }
    }

    protected function logout() {
        $success = false;
        if (UserController::logged()) {
            unset($_SESSION['id_user']);
            $success = true;
        } else {
            $this->set('error', 'You are nor currently logged-in');
        }
        if ($this->app->request()->rest()) {
            $this->set('success', $success);
        } else {
            $this->app->response()->redirect($this->app->request()->referer());
        }
    }

}