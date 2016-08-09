<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Dispatcher;

define('ROOT_PATH', dirname(dirname(__DIR__)));
define('APP_NAME', 'api');

try {

    $config =  new \Phalcon\Config\Adapter\Ini(ROOT_PATH.'\\app\\config\\config.ini');
    $pathCfg = new \Phalcon\Config(array(
        'application' => array(
            'controllersDir' => ROOT_PATH . '/app/'.APP_NAME.'/controllers/',
            'modelsDir'      => ROOT_PATH . '/app/'.APP_NAME.'/models/',
            'migrationsDir'  => ROOT_PATH . '/app/'.APP_NAME.'/migrations/',
            'pluginsDir'     => ROOT_PATH . '/app/'.APP_NAME.'/plugins/',
            'libraryDir'     => ROOT_PATH . '/app/'.APP_NAME.'/library/',
            'cacheDir'       => ROOT_PATH . '/app/'.APP_NAME.'/cache/',
            'baseUri'        => '/',
        )
    ));
    $apiCfg = new \Phalcon\Config\Adapter\Ini(ROOT_PATH.'\\app\\'.APP_NAME.'\\config\\config.ini');
    $config->merge($pathCfg);
    $config->merge($apiCfg);

    $loader = new \Phalcon\Loader();
    $loader->registerNamespaces(
        array(
            'App\\Api\\Controllers' => $config->application->controllersDir,
            'App\\Api\\Models' => $config->application->modelsDir,
            'Phalcon\\Db\\Adapter\\Pdo' => ROOT_PATH.'\\lib\\phalcon\\db\\adapter\\pdo',
            'Phalcon\\Db\\Dialect' => ROOT_PATH.'\\lib\\phalcon\\db\\dialect',
            'Phalcon\\Db\\Result' => ROOT_PATH.'\\lib\\phalcon\\db\\result',
            'Lib\\Utils' => ROOT_PATH.'\\lib\\utils'
        )
    );
    $loader->register();

    $di = new FactoryDefault();
    $di->set('config', $config);
    $di->set('dispatcher', function () {
        $dispatcher = new Dispatcher();
        $eventsManager = new \Phalcon\Events\Manager();
        $dispatcher->setDefaultNamespace("App\\Api\\Controllers");
        $dispatcher->setEventsManager($eventsManager);
        return $dispatcher;
    });
    
    $di->setShared('url', function () use ($config) {
        $url = new UrlResolver();
        $url->setBaseUri($config->application->baseUri);

        return $url;
    });

    /*$di->set('router', function () {
        $router = new Router();
        $router->setDefaults([
            'namespace'  => ROOT_PATH . '\Controllers',
            'controller' => 'index',
            'action'     => 'index'
        ]);
        return $router;
    });*/

    $di->set('view', function (){
        $view = new View();
        $view->disable();
        return $view;
    });

    $di->set('db', function () use ($config){
        return new \Phalcon\Db\Adapter\Pdo\Sqlserver(array(
            "host"         => $config->database->host,
            "username"     => $config->database->username,
            "password"     => $config->database->password,
            "dbname"       => $config->database->dbname
        ));
    });

    $di->set('flash', function () {
        return new Flash(array(
            'error'   => 'alert alert-danger',
            'success' => 'alert alert-success',
            'notice'  => 'alert alert-info',
            'warning' => 'alert alert-warning'
        ));
    });

    $di->setShared('session', function () {
        $session = new SessionAdapter();
        $session->start();

        return $session;
    });

    $debug = new \Phalcon\Debug();
    $debug->listen();

    $application = new \Phalcon\Mvc\Application($di);
    $application->handle()->send();

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    //echo '<pre>' . $e->getTraceAsString(). '</pre>';
    echo '<pre>';
    print_r($e->getTrace());
    echo '</pre>';
}
