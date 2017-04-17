<?php
use Aura\Router\RouterContainer;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
  $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$routerContainer = new RouterContainer();

$map = $routerContainer->getMap();

$view = new \Slim\Views\PhpRenderer(__DIR__.'/../templates/');

// Anonymous function
$map->get('home', '/', function ($request, $response) use ($view){ // 'use' Has access to the context of the external variable.

    return $view->render($response, 'home.phtml', [
        'test' => 'Slim PHP esta funcionando normalmente'
    ]);
});
$matcher = $routerContainer->getMatcher();
$router = $matcher->match($request);

foreach ($router->attributes as $key => $value){
    $request = $request->withAttribute($key, $value);
}
$callable = $router->handler;

/** @var Response $response */
$response = $callable($request, new Response());

echo $response->getBody();