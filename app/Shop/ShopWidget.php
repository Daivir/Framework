<?php
namespace App\Shop;

use App\Admin\AdminWidgetInterface;
use App\Shop\Table\ProductTable;
use Virton\Renderer\RendererInterface;

class ShopWidget implements AdminWidgetInterface
{
    private $renderer;
    /**
     * @var ProductTable
     */
    private $productTable;

    public function __construct(
        RendererInterface $renderer,
        ProductTable $productTable
    ) {
        $this->renderer = $renderer;
        $this->productTable = $productTable;
    }

    public function render(): string
    {
        $count = $this->productTable->count();
        return $this->renderer->render('@shop/admin/widget', compact('count'));
    }

    public function renderMenu(): string
    {
        return $this->renderer->render('@shop/admin/menu');
    }
}
