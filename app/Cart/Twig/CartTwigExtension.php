<?php
namespace App\Cart\Twig;

use App\Cart\Cart;

class CartTwigExtension extends \Twig_Extension
{
    /**
     * @var Cart
     */
    private $cart;

    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('cart_count', [$this->cart, 'count'])
        ];
    }
}
