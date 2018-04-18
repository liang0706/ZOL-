<?php
//system config
define('IN_PRODUCTION', true);
define('PRODUCTION_ROOT', dirname(dirname(dirname(__FILE__))));
define('SYSTEM_VAR', PRODUCTION_ROOT . '/var/');

//app config
define('APP_NAME', 'Auto_Site');
define('APP_PATH', PRODUCTION_ROOT . '/App/'. APP_NAME);

//DB config
define('DB_USERNAME','root');
define('DB_PASSWORD', '');

//debug config
define('IS_DEBUGGING', true);
define('IS_PRODUCTION', 0);
if(!IS_DEBUGGING){
    error_reporting(0);
    ini_set("display_errors", 0);
}else{
    error_reporting(E_ALL);
    ini_set("display_errors", 1);
}

//init
require_once(PRODUCTION_ROOT . '/init.php');

//set namespace
ZOL::setNameSpace(PRODUCTION_ROOT . '/Auto');
ZOL::setNameSpace(PRODUCTION_ROOT . '/Db');
ZOL::setNameSpace(PRODUCTION_ROOT . '/Libs');
ZOL::setNameSpace(PRODUCTION_ROOT . '/Helper');

//bootstrap
ZOL_Controller_Front::run();
