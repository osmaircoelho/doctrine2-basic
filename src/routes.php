<?php
use App\Entity\Category;
use App\Entity\Post;
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

$map->get('home', '/', function(ServerRequestInterface $request, $response) use ($view, $entityManager){
    $postsRepository = $entityManager->getRepository(Post::class);
    $categoryRepository = $entityManager->getRepository(Category::class);
    $post = $postsRepository->findAll();
    $categories = $categoryRepository->findAll();
    $data = $request->getQueryParams();

    if( isset($data['search']) and $data['search'] != "" ){
        $queryBuilder = $postsRepository->createQueryBuilder('p');
        $queryBuilder->join('p.categories','c')
            ->where($queryBuilder->expr()->eq('c.id', $data['search']));
        $post = $queryBuilder->getQuery()->getResult();
    }else{
        $post = $postsRepository->findAll();
    }
    return $view->render($response, 'home.phtml', [
        'posts' => $post,
        'categories' =>$categories
    ]);
});

require_once __DIR__ . '/categories.php';
require_once __DIR__ . '/posts.php';

$matcher = $routerContainer->getMatcher();
$router = $matcher->match($request);

foreach ($router->attributes as $key => $value) {
    $request = $request->withAttribute($key, $value);
}
$callable = $router->handler;

/** @var Response $response */
$response = $callable($request, new Response());
if ($response instanceof Response\RedirectResponse) {
    header("location:{$response->getHeader("location")[0]}");
} elseif ($response instanceof Response) {
    echo $response->getBody();
}