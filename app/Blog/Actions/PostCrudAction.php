<?php
namespace App\Blog\Actions;

use App\Blog\Entity\Post;
use App\Blog\PostUpload;
use App\Blog\Table\CategoryTable;
use App\Blog\Table\PostTable;
use Virton\Actions\CrudAction;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Virton\Renderer\RendererInterface;
use Virton\Router;
use Virton\Session\FlashHandler;

class PostCrudAction extends CrudAction
{
    protected $viewPath = "@blog/admin/posts";

    protected $routePrefix = "blog.admin";

    protected $postUpload;

    protected $categoryTable;

    public function __construct(
        RendererInterface $renderer,
        Router $router,
        PostTable $table,
        CategoryTable $categoryTable,
        FlashHandler $flash,
        PostUpload $postUpload
    ) {
        parent::__construct($renderer, $router, $table, $flash);
        $this->categoryTable = $categoryTable;
        $this->postUpload = $postUpload;
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        $post = $this->table->find($request->getAttribute('id'));
        $this->postUpload->delete($post->getImage());
        return parent::delete($request);
    }

    protected function formParams(array $params): array
    {
        $params['categories'] = $this->categoryTable->findList();
        return $params;
    }

    protected function getNewEntity()
    {
        /** @var Post $entity */
        $entity = parent::getNewEntity();
        $entity->setCreatedAt(new \DateTime());
        return $entity;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Post $post
     * @return array
     */
    protected function prePersist(ServerRequestInterface $request, $post): array
    {
        $params = array_merge($request->getParsedBody(), $request->getUploadedFiles());
        $image = $this->postUpload->upload($params['image'], $post->getImage());
        if ($image) {
            $params['image'] = $image;
        } else {
            unset($params['image']);
        }
        $params = array_filter($params, function ($key) {
            return in_array($key, ['name', 'slug', 'content', 'created_at', 'category_id', 'image', 'published']);
        }, ARRAY_FILTER_USE_KEY);
        return array_merge($params, ['updated_at' => date('Y-m-d H:i:s')]);
    }

    protected function getValidator(ServerRequestInterface $request)
    {
        $validator = parent::getValidator($request)
            ->slug('slug')
            ->length('name', 4, 250)
            ->length('content', 12)
            ->length('slug', null, 50)
            ->exists('category_id', $this->categoryTable->getTable(), $this->categoryTable->getPdo())
            ->dateTime('created_at')
            ->extension('image', ['jpg', 'png'])
            ->notEmpty('name', 'slug', 'content')
            ->required('name', 'slug', 'content', 'created_at', 'category_id');
        if (is_null($request->getAttribute('id'))) {
            $validator->uploaded('image');
        }
        return $validator;
    }
}
