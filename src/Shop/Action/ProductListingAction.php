<?php
namespace App\Shop\Action;

use App\Shop\Table\ProductTable;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductListingAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var ProductTable
     */
    private $table;

    public function __construct(RendererInterface $renderer, ProductTable $table)
    {

        $this->renderer = $renderer;
        $this->table = $table;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getQueryParams();
        $page = $params['p'] ?? 1;
        $products = $this->table->findPublic()->paginate((3 * 4), $page);
        return $this->renderer->render('@shop/index', compact('products', 'page'));
    }
}
