<?php
namespace App\Shop\Action;

use App\Shop\Exception\AlreadyPurchasedException;
use App\Shop\PurchaseProduct;
use App\Shop\Table\ProductTable;
use Framework\Auth;
use Framework\Router;
use Framework\Session\FlashHandler;
use Psr\Http\Message\ServerRequestInterface;

class PurchaseProcessAction
{
    /**
     * @var PurchaseProduct
     */
    private $purchaseProduct;
    /**
     * @var Auth
     */
    private $auth;
    /**
     * @var Router
     */
    private $router;
    /**
     * @var FlashHandler
     */
    private $flash;
    /**
     * @var ProductTable
     */
    private $table;

    use \Framework\Actions\RouterAwareAction;

    public function __construct(
        ProductTable $table,
        PurchaseProduct $purchaseProduct,
        Auth $auth,
        Router $router,
        FlashHandler $flash
    ) {
        $this->table = $table;
        $this->purchaseProduct = $purchaseProduct;
        $this->auth = $auth;
        $this->router = $router;
        $this->flash = $flash;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        /** @var \App\Shop\Entity\Product $product */
        $product = $this->table->find((int)$request->getAttribute('id'));
        $user = $this->auth->getUser();
        $stripeToken = $params['stripeToken'];
        try {
            $this->purchaseProduct->process($product, $user, $stripeToken);
            $this->flash->success("Thanks you for your purchase! ({$product->getName()})");
            return $this->redirect('shop.download', ['id' => $product->getId()]);
        } catch (AlreadyPurchasedException $e) {
            $this->flash->warning('Product already purchased');
            return $this->redirect('shop.show', ['slug' => $product->getSlug()]);
        }
    }
}
