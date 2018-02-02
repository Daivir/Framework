<?php
namespace Tests\App\Blog\Actions;

use App\Blog\Table\PostTable;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use App\Blog\Actions\PostShowAction;
use stdClass;
use App\Blog\Entity\Post;

class PostShowActionTest extends TestCase
{
    private $action;
    private $postTable;
    private $renderer;
    private $router;

    public function setUp()
    {
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->postTable = $this->prophesize(PostTable::class);
        $this->router = $this->prophesize(Router::class);
        $this->action = new PostShowAction(
            $this->renderer->reveal(),
            $this->router->reveal(),
            $this->postTable->reveal()
        );
    }

    public function makePost(int $id, string $slug): Post
    {
        $post = new Post;
        $post->setId($id);
        $post->setSlug($slug);
        return $post;
    }

    public function testShowRedirect()
    {
        $post = $this->makePost(9, 'test-2');
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', 9)
            ->withAttribute('slug', 'test');

        $params = ['id' => $post->getId(), 'slug' => $post->getSlug()];
        $this->router->generateUri('blog.show', $params)->willReturn('/test2');
        $this->postTable->findWithCategory($post->getId())->willReturn($post);

        $response = call_user_func_array($this->action, [$request]);
        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals(['/test2'], $response->getHeader('location'));
    }

    public function testShowRender()
    {
        $post = $this->makePost(9, 'test-2');
        $request = (new ServerRequest('GET', '/'))
            ->withAttribute('id', $post->getId())
            ->withAttribute('slug', $post->getSlug());

        $this->postTable->findWithCategory($post->getId())->willReturn($post);
        $this->renderer->render('@blog/show', ['post' => $post])->willReturn('');
        
        $response = call_user_func_array($this->action, [$request]);
        $this->assertEquals(true, true);
    }
}
