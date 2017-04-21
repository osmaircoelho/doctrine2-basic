<?php
use App\Entity\Category;
use Aura\Router\RouterContainer;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

$request = \Zend\Diactoros\ServerRequestFactory::fromGlobals(
  $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$routerContainer = new RouterContainer();
$generator = $routerContainer->getGenerator();
$map = $routerContainer->getMap();

$view = new \Slim\Views\PhpRenderer(__DIR__.'/../templates/');
$entityManager = getEntityManager();
// Anonymous function
$map->get('home', '/', function ($request, $response) use ($view){ // 'use' Has access to the context of the external variable.
    return $view->render($response, 'home.phtml', [
        'test' => 'Slim PHP esta funcionando normalmente'
    ]);
});

$map->get('categories.create', '/categories/create', function($request, $response) use ($view){
    return $view->render($response, 'categories/create.phtml');
});

$map->post('categories.store', '/categories/store',
    function(ServerRequestInterface $request, $response) use ($view, $entityManager, $generator){
        $data = $request->getParsedBody();
        $category = new Category();
        $category->SetName($data['name']);
        $entityManager->persist($category);
        $entityManager->flush();
        $uri = $generator->generate('categories.list');
        return new Response\RedirectResponse($uri);
});

$map->get('categories.list', '/categories', function ($request, $response) use ($view, $entityManager){
    $repository = $entityManager->getRepository(Category::class);
    $categories = $repository->findAll();
    return $view->render($response, 'categories/list.phtml', [
        'categories' => $categories
    ]);
});

$map->get('categories.edit', '/categories/{id}/edit',
    function(ServerRequestInterface $request, $response) use ($view, $entityManager){
        $id = $request->getAttribute('id');
        $repository = $entityManager->getRepository(Category::class);
        $category = $repository->find($id);
        return $view->render($response, 'categories/edit.phtml',
            [
                'category' => $category
            ]);
});

$map->post('categories.update', '/categories/{id}/update',
    function(ServerRequestInterface $request, $response) use ($view, $entityManager, $generator){
        $id = $request->getAttribute('id');
        $repository = $entityManager->getRepository(Category::class);
        $category = $repository->find($id);
        $data = $request->getParsedBody();
        $category->SetName($data['name']);
        $entityManager->flush();
        $uri = $generator->generate('categories.list');
        return new Response\RedirectResponse($uri);
    });

$matcher = $routerContainer->getMatcher();
$router = $matcher->match($request);

foreach ($router->attributes as $key => $value){
    $request = $request->withAttribute($key, $value);
}
$callable = $router->handler;

/** @var Response $response */
$response = $callable($request, new Response());
if ($response instanceof Response\RedirectResponse){
    header("location:{$response->getHeader("location")[0]}");
}elseif ($response instanceof Response) {
    echo $response->getBody();
}