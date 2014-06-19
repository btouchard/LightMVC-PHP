<?php
namespace Library;

use Library\Utils\Debug;

class LayoutException extends \Exception {}

class Layout extends Component {

    private $module, $layout, $view;
    private $magic = array();

    public function __construct(Application $app, $module, $action = null, $layout = 'default') {
        parent::__construct($app);
        $this->module = $module;
        $this->view = '/Views/' . $this->module;
        if ($action != null) $this->view .= '/' . $action;
        $this->view .= '.php';
        $this->layout = '/Views/Layout/' . $layout . '.php';

        set_error_handler(array($this, "handleError"));
    }

    public function handleError($errno, $errstr, $errfile, $errline) {
        if ($errno == 8 && preg_match('#^Undefined variable: ([a-zA-Z0-9_]+)#', $errstr, $match) > 0) {
            //throw new LayoutException('<b>La variable \'' . $match[1] . '\' n\'est pas définie !</b>' . "\n" . 'dans le fichier \'' . $errfile . '\', à la ligne: \'' . $errline . '\'', 200);
            $caller = $this->getCaller();
            Debug::log('Variable non définie: <b>' . $match[1] . '</b> dans ' . $errfile . ' à la ligne ' . $errline . "\n" . 'Avez-vous défini cette variable dans le contrôler <b>' . $caller['class'] . '->' . $caller['function'] . '()</b>');
            return true;
        }
        return false;
    }

    public function module() {
        return $this->module;
    }

    public function set($var, $value) {
        if (!is_string($var) || is_numeric($var) || empty($var)) {
            throw new \InvalidArgumentException('Le nom de la variable doit être une chaine de caractère non nulle');
        }
        $this->magic[$var] = $value;
    }

    public function draw() {
        $path = __DIR__ . '/../App';
        if (!file_exists($path . $this->layout)) {
            throw new LayoutException('Le layout demandé \'' . $this->layout . '\' n\'existe pas !', 404);
        }
        if (!file_exists($path . $this->view)) {
            throw new LayoutException('La vue demandée \'' . $this->view . '\' n\'existe pas !', 404);
        }

        extract($this->magic);

        ob_start();
        require $path . $this->view;
        $content = "\n" . ob_get_clean() . "\n";

        ob_start();
        require $path . $this->layout;
        return ob_get_clean();
    }
}