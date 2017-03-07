<?
use \Core\Core;
define('PUBLIC_ROOT', dirname(__FILE__));
define('APP_ROOT', dirname(PUBLIC_ROOT));

spl_autoload_register(function($class){
    if(is_file($path = str_replace('\\', '/', APP_ROOT."/{$class}.php"))){
        require_once($path);
    }else
        die("Class {$class} not found in path {$path}");
});

Core::load();
Core::$route->start();