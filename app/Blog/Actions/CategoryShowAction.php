<?php
namespace App\Blog\Actions;

use Psr\Http\Message\ServerRequestInterface as Request;
use Virton\Renderer\RendererInterface as Renderer;
use App\Blog\Table\PostTable;
use App\Blog\Table\CategoryTable;

class CategoryShowAction
{
	private $renderer;
	private $postTable;
	private $categoryTable;

	use \Virton\Actions\RouterAwareAction;

	public function __construct(Renderer $renderer, PostTable $postTable, CategoryTable $categoryTable)
	{
		$this->renderer = $renderer;
		$this->postTable = $postTable;
		$this->categoryTable = $categoryTable;
	}

    /**
     * @param Request $request
     * @return string
     */
	public function __invoke(Request $request)
	{
		$params = $request->getQueryParams();
		$page = $params['p'] ?? 1;
		$category = $this->categoryTable->findBy('slug', $request->getAttribute('slug'));
		$posts = $this->postTable->findPublicForCategory($category->getId())->paginate(12, $params['p'] ?? 1);
		$categories = $this->categoryTable->findAll();

		return $this->renderer->render('@blog/index', compact('posts', 'categories', 'category', 'page'));
	}
}
