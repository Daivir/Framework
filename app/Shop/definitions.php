<?php

use App\Shop\ShopWidget;
use App\Shop\Action\ProductShowAction;
use Virton\Api\Stripe;

use function \DI\{add, get, object};

return [
    'admin.widgets' => add([
        get(ShopWidget::class)
    ]),
    ProductShowAction::class => object()->constructorParameter(
        'stripeKey',
        get('stripe.key.public')
    ),
    Stripe::class => object()->constructor(get('stripe.key.secret'))
];
