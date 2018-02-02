<?php
namespace App\Shop\Action;

use App\Shop\Table\ProductTable;
use Framework\Api\Stripe;
use Framework\Renderer\RendererInterface;
use Psr\Http\Message\ServerRequestInterface;
use Staaky\VATRates\VATRates;

class PurchaseSummaryAction
{
    /**
     * @var RendererInterface
     */
    private $renderer;
    /**
     * @var ProductTable
     */
    private $table;
    /**
     * @var Stripe
     */
    private $stripe;

    public function __construct(
        RendererInterface $renderer,
        ProductTable $table,
        Stripe $stripe
    ) {
        $this->renderer = $renderer;
        $this->table = $table;
        $this->stripe = $stripe;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $stripeToken = $params['stripeToken'];
        $card = $this->stripe->getCardFromToken($stripeToken);
        $vat = (new VATRates())->getStandardRate($card->country);
        /** @var \App\Shop\Entity\Product $product */
        $product = $this->table->find((int)$request->getAttribute('id'));
        $price = floor($product->getPrice() * (($vat + 100) / 100));
        return $this->renderer->render(
            '@shop/summary',
            compact('vat', 'product', 'price', 'card', 'stripeToken')
        );
    }
}
