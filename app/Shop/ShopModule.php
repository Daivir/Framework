<?php
namespace App\Shop;

use Virton\Auth\LoggedInMiddleware;
use Virton\Module;
use Virton\Renderer\RendererInterface;
use Virton\Router;
use Psr\Container\ContainerInterface;

class ShopModule extends Module
{
    const MIGRATIONS = __DIR__ . '/db/migrations';
    const SEEDS = __DIR__ . '/db/seeds';
    const DEFINITIONS = __DIR__ . '/definitions.php';

    /*
     * TODO: faire une méthode qui renvoie le tableau avec LoggedInMiddleware
     */

    public function __construct(ContainerInterface $container)
    {
        $router = $container->get(Router::class);
        $renderer = $container->get(RendererInterface::class);
        $prefix = $container->get('admin.prefix');

        $renderer->addPath('shop', __DIR__ . '/views');

        $router->crud($prefix . '/products', Action\ProductCrudAction::class, 'shop.admin.products');
        $router->get(
            '/shop/{id}/download',
            [LoggedInMiddleware::class, Action\ProductDownloadAction::class],
            'shop.download'
        );
        $router->post(
            '/shop/{id}/process',
            [LoggedInMiddleware::class, Action\PurchaseProcessAction::class],
            'shop.process'
        );
        $router->post(
            '/shop/{id}/summary',
            [LoggedInMiddleware::class, Action\PurchaseSummaryAction::class],
            'shop.purchase'
        );
        $router->get(
            '/shop/purchases',
            [LoggedInMiddleware::class, Action\PurchaseListingAction::class],
            'shop.purchases'
        );
        $router->get(
            '/shop/invoice/{id}',
            [LoggedInMiddleware::class, Action\InvoiceAction::class],
            'shop.invoice'
        );
        $router->get('/shop/{slug}', Action\ProductShowAction::class, 'shop.show');
        $router->get('/shop', Action\ProductListingAction::class, 'shop');
    }
}
