<?php
namespace App\Cart\Action;

use App\Cart\Cart;
use App\Cart\Table\CartTable;
use Framework\Api\Stripe;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staaky\VATRates\VATRates;

class OrderSummaryAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var CartTable
     */
    private $cartTable;

    /**
     * @var Stripe
     */
    private $stripe;
    /**
     * @var Cart
     */
    private $cart;

    public function __construct(
        RendererInterface $renderer,
        CartTable $cartTable,
        Stripe $stripe,
        Cart $cart
    ) {
        $this->renderer = $renderer;
        $this->cartTable = $cartTable;
        $this->stripe = $stripe;
        $this->cart = $cart;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $stripeToken = $params['stripeToken'];
        $card = $this->stripe->getCardFromToken($stripeToken);
        $vat = (new VATRates())->getStandardRate($card->country);
        $cart = $this->cart;
        $this->cartTable->hydrateCart($cart);
        $price = floor($cart->getPrice() * (($vat + 100) / 100));
        return $this->renderer->render(
            '@cart/summary',
            compact('vat', 'cart', 'price', 'card', 'stripeToken')
        );
    }
}
