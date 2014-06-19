<?php
session_start();
setlocale(LC_ALL, 'fr_FR.UTF8');

define('ROOT_NAME', basename(dirname(dirname(__FILE__))));
define('WEB_DIR', 'webroot');
define("MYSQL_HOST","localhost");
define("MYSQL_DB","mydb");
define("MYSQL_USER","root");
define("MYSQL_PASS","");

function autoload($class) {
    require '../../' . str_replace('\\', '/', $class).'.class.php';
}
function exist($class) {
    return file_exists('../../' . str_replace('\\', '/', $class).'.class.php');
}
spl_autoload_register('autoload');