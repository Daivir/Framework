<?php

// TODO: V8js renderer

$_ENV['ENV'] = 'dev';

// Modules
use App\{
	Account\AccountModule, Admin\AdminModule, Auth\AuthModule, Auth\NoRecordMiddleware, Blog\BlogModule, Cart\CartModule, Contact\ContactModule, IndexModule, Shop\ShopModule
};

use Framework\App;

use Framework\Auth\RoleMiddlewareFactory;

// Middlewares
use Framework\Middleware\{
	CorsMiddleware, CsrfMiddleware, DispatcherMiddleware, MethodMiddleware, NotFoundMiddleware, RendererRequestMiddleware, RouterMiddleware, TrailingSlashMiddleware
};

use Middlewares\Whoops;
use GuzzleHttp\Psr7\ServerRequest;
use function Http\Response\send;
use App\Auth\ForbiddenMiddleware;
use Psr\Http\Message\ResponseInterface;

chdir(dirname(__DIR__));

require 'vendor/autoload.php';

date_default_timezone_set('Europe/Paris');

// Initiates Modules
$app = (new App(['config/config.php', 'config.php']))
	->addModule(AdminModule::class)
    ->addModule(ContactModule::class)
    ->addModule(ShopModule::class)
	->addModule(BlogModule::class)
	->addModule(AuthModule::class)
    ->addModule(AccountModule::class)
    ->addModule(CartModule::class)
;

/** @var \Psr\Container\ContainerInterface $container */
$container = $app->getContainer();

$router = $container->get(Framework\Router::class);

// Allow CORS
$router->options('/{routes:.+}', function (ResponseInterface $response) {
	return $response;
});

// Default route
$router->get('/', \App\Blog\Actions\PostIndexAction::class, 'index');

// Piping Middlewares
$app->pipe(Whoops::class)
	->pipe(CorsMiddleware::class)
	->pipe(TrailingSlashMiddleware::class)
	->pipe(ForbiddenMiddleware::class)
	->pipe(NoRecordMiddleware::class)
	->pipe(
		$container->get('admin.prefix'),
		$container->get(RoleMiddlewareFactory::class)->makeRole('admin')
	)
	->pipe(MethodMiddleware::class)
	->pipe(RendererRequestMiddleware::class)
	->pipe(CsrfMiddleware::class)
	->pipe(RouterMiddleware::class)
	->pipe(DispatcherMiddleware::class)
	->pipe(NotFoundMiddleware::class)
;

// Runs the Application
if (php_sapi_name() !== 'cli') {
    $response = $app->run(ServerRequest::FromGlobals());
    send($response);
    $extensions = ["php", "jpg", "jpeg", "gif", "css"];
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if (in_array($ext, $extensions)) {
        return false;
    }
}
