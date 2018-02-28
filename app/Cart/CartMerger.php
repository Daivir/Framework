<?php
namespace App\Cart;

use App\Auth\Event\LoginEvent;
use App\Cart\Table\CartTable;

class CartMerger
{
    /**
     * @var SessionCart
     */
    private $cart;
    /**
     * @var CartTable
     */
    private $cartTable;

    public function __construct(SessionCart $cart, CartTable $cartTable)
    {
        $this->cart = $cart;
        $this->cartTable = $cartTable;
    }

    public function __invoke(LoginEvent $event)
    {
        $user = $event->getTarget();
        (new DatabaseCart($user->getId(), $this->cartTable))->merge($this->cart);
        $this->cart->empty();
    }
}
