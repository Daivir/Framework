<?php
namespace App\Blog\Actions;

use App\Blog\Table\CategoryTable;
use App\Blog\Table\PostTable;
use Psr\Http\Message\ServerRequestInterface as Request;
use Virton\Renderer\RendererInterface;
use Virton\Router;
use GuzzleHttp\Psr7\Response;

class PostShowAction
{
	private $renderer;
	private $router;
	private $postTable;

	use \Virton\Actions\RouterAwareAction;

	public function __construct(RendererInterface $renderer, Router $router, PostTable $postTable)
	{
		$this->router = $router;
		$this->postTable = $postTable;
		$this->renderer = $renderer;
	}

	public function __invoke(Request $request)
	{
		$id = $request->getAttribute('id');
		$slug = $request->getAttribute('slug');

		$post = $this->postTable->findWithCategory($id);

		if ($post->getSlug() !== $slug) {
			return $this->redirect('blog.show', [
				'slug' => $post->getSlug(),
				'id' => $post->getId()
			]);
		}

		return $this->renderer->render('@blog/show', compact('post'));
	}
}
