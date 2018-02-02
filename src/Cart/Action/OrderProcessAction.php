<?php
namespace App\Cart\Action;

use App\Cart\Cart;
use App\Cart\PurchaseCart;
use Framework\Auth;
use Framework\Response\RedirectResponse;
use Framework\Session\FlashHandler;
use Psr\Http\Message\ServerRequestInterface;

class OrderProcessAction
{
    use \Framework\Actions\RouterAwareAction;

    /**
     * @var PurchaseCart
     */
    private $purchaseCart;

    /**
     * @var Auth
     */
    private $auth;

    /**
     * @var FlashHandler
     */
    private $flash;

    /**
     * @var Cart
     */
    private $cart;

    public function __construct(PurchaseCart $purchaseCart, Auth $auth, FlashHandler $flash, Cart $cart)
    {
        $this->purchaseCart = $purchaseCart;
        $this->auth = $auth;
        $this->flash = $flash;
        $this->cart = $cart;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $params = $request->getParsedBody();
        $stripeToken = $params['stripeToken'];
        $this->purchaseCart->process($this->cart, $this->auth->getUser(), $stripeToken);
        $this->cart->empty();
        $this->flash->success("Thanks for your purchase!");
        return new RedirectResponse('/');
    }
}
