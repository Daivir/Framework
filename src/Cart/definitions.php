<?php

use App\Cart\{
	Action\CartAction, Cart, CartFactory, CartWidget, Twig\CartTwigExtension
};
use function DI\{
	add, factory, get, object
};

return [
	'twig.extensions' => add([
		get(CartTwigExtension::class)
	]),
	'admin.widgets' => add([
		get(CartWidget::class)
	]),
	Cart::class => factory(CartFactory::class),
	CartAction::class => object()->constructorParameter(
		'stripeKey',
		get('stripe.key.public')
	)
];
