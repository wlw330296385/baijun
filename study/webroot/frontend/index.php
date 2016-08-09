<?php

error_reporting(E_ALL);

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\Dispatcher;

define('APP_PATH', dirname(dirname(__DIR__)));

try {
    $config =  new \Phalcon\Config\Adapter\Ini(APP_PATH.'\\app\\config\\config.ini');
    $pathCfg = new \Phalcon\Config(array(
        'application' => array(
            'controllersDir' => APP_PATH . '/app/frontend/controllers/',
            'modelsDir'      => APP_PATH . '/app/frontend/models/',
            'migrationsDir'  => APP_PATH . '/app/frontend/migrations/',
            'viewsDir'       => APP_PATH . '/app/frontend/views/',
            'pluginsDir'     => APP_PATH . '/app/frontend/plugins/',
            'libraryDir'     => APP_PATH . '/app/frontend/library/',
            'cacheDir'       => APP_PATH . '/app/frontend/cache/',
            'baseUri'        => '/',
        )
    ));
    $apiCfg = new \Phalcon\Config\Adapter\Ini(APP_PATH.'\\app\\frontend\\config\\config.ini');
    $config->merge($pathCfg);
    $config->merge($apiCfg);

    $loader = new \Phalcon\Loader();
    $loader->registerDirs(
        array(
            $config->application->controllersDir,
            $config->application->modelsDir
        )
    );
    $loader->registerNamespaces(
        array(
            'App\\Frontend\\Controllers' => realpath($config->application->controllersDir)
        )
    );
    $loader->register();


    $di = new FactoryDefault();

    $di->set('dispatcher', function () {
        $dispatcher = new Dispatcher();
        $dispatcher->setDefaultNamespace("App\\Frontend\\Controllers");
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
            'namespace'  => APP_PATH . '\Controllers',
            'controller' => 'index',
            'action'     => 'index'
        ]);
        return $router;
    });*/

    $di->setShared('view', function () use ($config) {

        $view = new View();

        $view->setViewsDir($config->application->viewsDir);

        $view->registerEngines(array(

            '.volt' => function ($view, $di) use ($config) {

                $volt = new VoltEngine($view, $di);

                $volt->setOptions(array(
                    'compiledPath' => $config->application->cacheDir,
                    'compiledSeparator' => '_'
                ));

                return $volt;
            },
            '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
        ));

        return $view;
    });

    /*    $di->setShared('db', function () use ($config) {
            $dbConfig = $config->database->toArray();
            $adapter = $dbConfig['adapter'];
            unset($dbConfig['adapter']);

            $class = 'Phalcon\Db\Adapter\Pdo\\' . $adapter;

            return new $class($dbConfig);
        });*/

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

    $application = new \Phalcon\Mvc\Application($di);
    echo $application->handle()->getContent();

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . var_export($e->getTrace(),true) . '</pre>';
}
