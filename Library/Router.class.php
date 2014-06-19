<?php
namespace Library;

use Library\Utils\DB;
use Library\Utils\Debug;

class RouterException extends \Exception {}

class Router extends Component {

    const DEFAULT_MODULE = 'Home';
    const DEFAULT_ACTION_LIST = 'index';
    const DEFAULT_ACTION_SHOW = 'show';

    private function getClass($ctrl) {
        return '\\App\\Controller\\' . $ctrl . 'Controller';
    }
    private function isController($ctrl) {
        return exist($this->getClass($ctrl));
    }
    private function isControllerAction($ctrl, $action) {
        $class = $this->getClass($ctrl);
        $inst = new $class($this->app);
        return method_exists($inst, $action);
    }

    public function getController() {
        $ctrl = $method = null;
        $url = trim($this->app->request()->request(), '/');
        $params = array();
        $method = self::DEFAULT_ACTION_LIST;
        if (empty($url)) {
            $ctrl = self::DEFAULT_MODULE;
        } else {
            $params = explode(SEPARATOR, $url);
            if (count($params) > 0 && is_string($params[0])) {
                $ctrl = ucwords(array_shift($params));
            }
        }
        if (!$this->isController($ctrl)) throw new RouterException('Le controller demandé \'' . $ctrl . '\' n\'existe pas !', 404);
        if (count($params) > 0 && is_numeric($params[0])) {
            $_GET['id'] = (int) array_shift($params);
            $method = self::DEFAULT_ACTION_SHOW;
        }
        if (count($params) > 0 && is_string($params[0]) && preg_match('#^[a-zA-Z_]+$#', $params[0]) > 0) {
            $a = (string) $params[0];
            $reg = '#^([a-z]+)_([a-z]+)$#';
            if (preg_match($reg, $a, $match) > 0) $a = strtolower($match[1]) . ucwords($match[2]);
            if ($this->isControllerAction($ctrl, $a)) $method = array_shift($params);
            else throw new RouterException('La méthode demandée \'' . $a . '\' n\'existe pas !', 404);
        }
        if (count($params) > 0) {
            for ($i=0; $i<count($params); $i++) {
                if ($i == 0 && is_numeric($params[$i])) {
                    $_GET['id'] = (int) $params[$i];
                } else if (is_string($params[$i]) && isset($params[$i+1])) {
                    $_GET[$params[$i]] = $params[$i+1];
                }
            }
        }

        return array($this->getClass($ctrl), $method);
    }
}