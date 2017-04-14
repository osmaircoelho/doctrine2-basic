<?php
use Aura\Router\RouterContainer;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
  $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$routerContainer = new RouterContainer();

$map = $routerContainer->getMap();
$map->get('home', '/', function ($request, $response){
    $response->getBody()->write("Olá esta tudo funcionando agora");
    return $response;
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