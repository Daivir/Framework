<?php
namespace App\Cart;

use App\Auth\User;
use App\Cart\Table\CartTable;
use Framework\Auth;
use Psr\Container\ContainerInterface;

class CartFactory
{
    public function __invoke(ContainerInterface $container)
    {
        /** @var User $user */
        $user = $container->get(Auth::class)->getUser();
        if ($user) {
            return new DatabaseCart($user->getId(), $container->get(CartTable::class));
        } else {
            return $container->get(SessionCart::class);
        }
    }
}
