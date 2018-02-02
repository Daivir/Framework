<?php
namespace App\Blog\Actions;

use App\Blog\Entity\Post;
use App\Blog\Table\CategoryTable;
use Framework\Actions\CrudAction;
use Framework\Renderer\RendererInterface;
use Framework\Router;
use Framework\Session\FlashHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

class CategoryCrudAction extends CrudAction
{
    protected $viewPath = "@blog/admin/categories";

    protected $routePrefix = "blog.category.admin";

    protected $acceptedParams = ['name', 'slug'];

    public function __construct(
        RendererInterface $renderer,
        Router $router,
        CategoryTable $table,
        FlashHandler $flash
    ) {
        parent::__construct($renderer, $router, $table, $flash);
    }

    protected function getValidator(Request $request)
    {
        $table = $this->table->getTable();
        $pdo = $this->table->getPdo();

        $id = $request->getAttribute('id');

        return (parent::getValidator($request))
            ->required('name', 'slug')
            ->slug('slug')
            ->length('name', 4, 250)
            ->length('slug', null, 50)
            ->unique('slug', $table, $pdo, $id)
            ->notEmpty('name', 'slug');
    }
}
