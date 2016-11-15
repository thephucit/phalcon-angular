<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
defined('APP_PATH') || define('APP_PATH', __DIR__.'/apps/');

use Phalcon\Loader;
use Phalcon\Mvc\Router;
use Phalcon\DI\FactoryDefault;
use Phalcon\Mvc\Application as BaseApplication;

class Application extends BaseApplication
{
    protected function registerServices()
    {
        $di = new FactoryDefault();
        $loader = new Loader();
        $loader->registerDirs(
            array(
                __DIR__ . '/../apps/library/'
            )
        )->register();
        $di->set('router', function(){
            $router = new Router(false);
            $router->setDefaultModule("frontend");
            $router->setDefaultController('index');
            $router->setDefaultAction('index');
            $router->add("/api/:module/:controller/:action",
            [
                "module"     => 1,
                "controller" => 2,
                "action"     => 3,
            ]);
            $Routes = glob("apps/**/routes/*.php");
            foreach ($Routes as $key => $value){ require $value; }
            $router->removeExtraSlashes(true);
            $router->handle();
            return $router;
        });
        $di->set('url', function() {
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri('/');
            return $url;
        });
        $this->setDI($di);
    }

    public function main()
    {
        $this->registerServices();
        $this->registerModules(array(
            'frontend' => array(
                'className' => 'Multiple\Frontend\Module',
                'path' => 'apps/frontend/Module.php'
            )
        ));
        echo $this->handle()->getContent();
    }
}
$application = new Application();
$application->main();