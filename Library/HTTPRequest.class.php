<?php
namespace Library;

class HTTPRequest {

    private $request;
    private $rest = false;
    private $method, $accept, $referer, $contentType;
    //private $module = null, $action = null;

    public function __construct() {
        $this->request = $_SERVER['REQUEST_URI'];
        if (preg_match('#^' . SEPARATOR . ROOT_NAME.'#', $_SERVER['REQUEST_URI']) > 0) {
            define('BASE_URL', SEPARATOR . ROOT_NAME . SEPARATOR);
            $this->request = str_replace(BASE_URL, '', $this->request);
        } else define('BASE_URL', SEPARATOR);
        $this->request = str_replace('?' . $_SERVER['QUERY_STRING'], '', $this->request);
        $this->request = preg_replace('#.html?#', '', $this->request);
        $this->referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;
        $this->accept = $_SERVER['HTTP_ACCEPT'];
        $this->contentType = $_SERVER['CONTENT_TYPE'];
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->rest = (bool) $this->contentType == 'application/json';
        $this->readInputs();
    }

    public function html() {
        return !$this->rest;
    }
    public function rest($bool = null) {
        if ($bool !== null) $this->rest = $bool;
        return $this->rest;
    }
    /*public function module($module = null) {
        if ($module !== null) $this->module = $module;
        return $this->module;
    }
    public function action($action = null) {
        if ($action !== null) $this->action = $action;
        return $this->action;
    }*/

    public function method() {
        return $this->method;
    }
    public function accept() {
        return $this->accept;
    }
    public function referer() {
        return $this->referer;
    }
    public function request() {
        return $this->request;
    }

    private function readInputs() {
        $input = file_get_contents("php://input");
        switch($this->method) {
            case "POST":
            case "PUT":
                if ($this->rest)
                    $_POST = json_decode($input, true);
                else {
                    parse_str($input, $input);
                    $_POST = $this->cleanInputs($input);
                }
                break;
            case "GET":
            case "DELETE":
                $_GET = $this->cleanInputs($_GET);
                break;
            default:
                $this->response('',406);
                break;
        }
    }
    private function cleanInputs($data){
        $clean_input = array();
        if(is_array($data)){
            foreach($data as $k => $v){
                $clean_input[$k] = $this->cleanInputs($v);
            }
        }else{
            if(get_magic_quotes_gpc()){
                $data = trim(stripslashes($data));
            }
            $data = strip_tags($data);
            $clean_input = trim($data);
        }
        return $clean_input;
    }
}