<?php
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Moscow');
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

// Ensure library/ is on include_path
// Directories for include path
$root = realpath(dirname(__FILE__) . '/../');
$library = $root . '/library';
$models = $root . '/application/models';

$path = array(
    $library,
    $models,
    get_include_path()
);
set_include_path(implode(PATH_SEPARATOR, $path));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../../Zend/ZendFramework-1.12.3/library'),
    realpath(APPLICATION_PATH . '/../../Zend/ZendFramework-1.12.3/extras/library'),
    get_include_path(),
)));

$_SERVER['SERVER_NAME'] = 'http://rutvgid.zen.lan';

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

unset($root, $library, $models, $path);

Zend_Session::$_unitTestEnabled = true;
Zend_Session::start();