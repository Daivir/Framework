<?php
namespace App\Blog;

use App\Blog\Actions\CategoryCrudAction;
use App\Blog\Actions\CategoryShowAction;
use App\Blog\Actions\PostCrudAction;
use App\Blog\Actions\PostIndexAction;
use App\Blog\Actions\PostShowAction;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Psr\Container\ContainerInterface;

class BlogModule extends Module
{
	const DEFINITIONS = __DIR__ . '/config.php';
	const MIGRATIONS = __DIR__ . '/db/migrations';
	const SEEDS = __DIR__ . '/db/seeds';

	public function __construct(ContainerInterface $container)
	{
		$container->get(RendererInterface::class)->addPath('blog', __DIR__ . '/views');

		$router = $container->get(Router::class);
		$prefix = $container->get('blog.prefix');
		$router->get($prefix, PostIndexAction::class, 'blog.index');
		$router->get("$prefix/{slug:[a-z0-9\-]+}-{id:\d+}", PostShowAction::class, 'blog.show');
		$router->get("$prefix/category/{slug:[a-z0-9\-]+}", CategoryShowAction::class, 'blog.category');

		if ($container->has('admin.prefix')) {
			$prefix = $container->get('admin.prefix');
			$router->crud("$prefix/posts", PostCrudAction::class, 'blog.admin');
			$router->crud("$prefix/categories", CategoryCrudAction::class, 'blog.category.admin');
		}
	}
}
