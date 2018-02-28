<?php
namespace App\Blog\Actions;

use App\Blog\Entity\Post;
use App\Blog\Table\CategoryTable;
use App\Blog\Table\PostTable;
use function DI\string;
use Virton\Actions\RouterAwareAction;
use Virton\Database\Hydrator;
use Psr\Http\Message\ServerRequestInterface as Request;
use Virton\Renderer\RendererInterface;
use Virton\Router;
use GuzzleHttp\Psr7\Response;

class PostIndexAction
{
	private $categoryTable;
	private $postTable;
	private $renderer;

	use RouterAwareAction;

	public function __construct(RendererInterface $renderer, PostTable $postTable, CategoryTable $categoryTable)
	{
		$this->categoryTable = $categoryTable;
		$this->postTable = $postTable;
		$this->renderer = $renderer;
	}

	public function __invoke(Request $request)
	{
		$params = $request->getQueryParams();
		$posts = $this->postTable->findPublic()->paginate(12, $params['p'] ?? 1);
		$categories = $this->categoryTable->findAll()->fetchAll();

		return $this->renderer->render('@blog/index', compact('posts', 'categories', 'ssr'));
	}
}
