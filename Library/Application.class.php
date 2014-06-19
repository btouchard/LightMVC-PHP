<?php
namespace Library;

use Library\Utils\Debug;
use Library\Utils\Json;

class Application extends Component {

    protected $request, $response;
    protected $router;

    public function __construct() {
        $this->request = new HTTPRequest;
        $this->response = new HTTPResponse;
    }

    public function run() {
        try {
            $this->router = new Router($this);
            list($class, $method) = $this->router->getController();
            $ctrl = new $class($this);
            foreach ($this->vars as $key => $value) $ctrl->set($key, $value);
            $ctrl->{$method}();
            $this->response->send();
        } catch (RouterException $e) {
            $this->setError($e->getCode(), $e->getMessage());
        } catch (ControllerException $e) {
            $this->setError($e->getCode(), $e->getMessage());
        } catch (LayoutException $e) {
            Debug::log($e);
            $this->setError($e->getCode(), $e->getMessage());
        }
    }

    public function setError($code, $content = null) {
        $this->response->setStatus($code);
        if ($content === null) $content = $this->response->getCodeInfo($code);
        if ($this->request->rest()) $content = Json::encode(array('success' => false, 'result' => $content));
        else {
            $layout = new Layout($this, 'Error');
            $layout->set('title', 'Erreur');
            $layout->set('error', nl2br($content));
            $content = $layout->draw();
        }
        $this->response->setContent($content);
        $this->response->send();
    }

    public function request() {
        return $this->request;
    }

    public function response() {
        return $this->response;
    }
}