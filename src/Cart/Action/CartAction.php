<?php

namespace App\Cart\Action;

use App\Cart\Cart;
use App\Cart\Table\CartTable;
use App\Shop\Table\ProductTable;
use Framework\Renderer\RendererInterface;
use Framework\Response\RedirectBackResponse;
use Psr\Http\Message\ServerRequestInterface;

class CartAction
{
	/**
	 * @var Cart
	 */
	private $cart;

	/**
	 * @var RendererInterface
	 */
	private $renderer;

	/**
	 * @var CartTable
	 */
	private $cartTable;

	/**
	 * @var string
	 */
	private $stripeKey;

	public function __construct(
		Cart $cart,
		RendererInterface $renderer,
		CartTable $cartTable,
		string $stripeKey
	) {
		$this->cart = $cart;
		$this->renderer = $renderer;
		$this->cartTable = $cartTable;
		$this->stripeKey = $stripeKey;
	}

	public function __invoke(ServerRequestInterface $request)
	{
		if ($request->getMethod() === 'GET') {
			return $this->show();
		} elseif ($request->getMethod() === 'POST') {
			$product = $this->cartTable->getProductTable()->find((int)$request->getAttribute('id'));
			$params = $request->getParsedBody();
			$this->cart->addProduct($product, $params['quantity'] ?? null);
			return new RedirectBackResponse($request);
		}
	}

	private function show()
	{
		$this->cartTable->hydrateCart($this->cart);
		return $this->renderer->render('@cart/show', [
			'cart' => $this->cart,
			'stripeKey' => $this->stripeKey
		]);
	}
}
