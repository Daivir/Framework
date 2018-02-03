<?php
namespace App\Cart;

use App\Admin\AdminWidgetInterface;
use App\Cart\Table\OrderTable;
use App\Shop\Table\ProductTable;
use Virton\Renderer\RendererInterface;

class CartWidget implements AdminWidgetInterface
{
	/**
	 * @var RendererInterface
	 */
	private $renderer;

	/**
	 * @var OrderTable
	 */
	private $orderTable;

	public function __construct(
		RendererInterface $renderer,
		OrderTable $orderTable
	) {
		$this->renderer = $renderer;
		$this->orderTable = $orderTable;
	}

	public function render(): string
	{
		$total = $this->orderTable->getMonthRevenue();
		return $this->renderer->render('@cart/admin/widget', compact('total'));
	}

	public function renderMenu(): string
	{
		return $this->renderer->render('@cart/admin/menu');
	}
}
