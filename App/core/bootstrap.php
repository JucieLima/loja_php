<?php

$capsule =  new \Illuminate\Database\Capsule\Manager;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'loja_virtual',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

session_start();

\PagSeguro\Library::initialize();
\PagSeguro\Library::cmsVersion()->setName(SITENAME)->setRelease("1.0.0");
\PagSeguro\Library::moduleVersion()->setName(SITENAME)->setRelease("1.0.0");

\PagSeguro\Configuration\Configure::setEnvironment("sandbox");
\PagSeguro\Configuration\Configure::setAccountCredentials("juciegeraldo@hotmail.com", "A7846B0FDE214140B1C3CDACD4BD133D");
\PagSeguro\Configuration\Configure::setCharset("UTF-8");
\PagSeguro\Configuration\Configure::setLog(true, "logs/pagseguro.log");



new \App\Core\Run($zones);



