<?php
namespace App\Cart;

use Framework\Auth\LoggedInMiddleware;
use Framework\EventManager\EventManager;
use Framework\Module;
use Framework\Renderer\RendererInterface;
use Framework\Router;

class CartModule extends Module
{
    const DEFINITIONS = __DIR__ . '/definitions.php';

    const MIGRATIONS = __DIR__ . '/migrations';

    const NAME = 'cart';

    public function __construct(
        Router $router,
        RendererInterface $renderer,
        EventManager $eventManager,
        CartMerger $cartMerger
    ) {
        $router->post('/shopping-cart/add/{id:\d+}', Action\CartAction::class, 'cart.add');
        $router->post('/shopping-cart/edit/{id:\d+}', Action\CartAction::class, 'cart.edit');
        $router->get('/shopping-cart', Action\CartAction::class, 'cart');

        // Purchase funnel
        $router->post(
            '/shopping-cart/summary',
            [LoggedInMiddleware::class, Action\OrderSummaryAction::class],
            'cart.order.summary'
        );
        $router->post(
            '/shopping-cart/order',
            [LoggedInMiddleware::class, Action\OrderProcessAction::class],
            'cart.order.process'
        );

        // Orders management
        $router->get(
            '/my-orders',
            [LoggedInMiddleware::class, Action\OrderListingAction::class],
            'cart.orders'
        );
        $router->get(
            '/my-orders/{id:\d+}',
            [LoggedInMiddleware::class, Action\OrderInvoiceAction::class],
            'cart.order.invoice'
        );

        $renderer->addPath('cart', __DIR__ . '/views');
        $eventManager->attach('auth.login', $cartMerger);
    }
}
