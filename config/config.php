<?php

use Framework\Renderer\{
    RendererInterface,
    TwigRendererFactory
};

use Framework\Router;

use Framework\Router\RouterFactory;

use Framework\Router\RouterTwigExtension;

use Framework\Session\{
    PHPSession,
    SessionInterface
};

use Framework\Twig\{
    CsrfExtension, FlashExtension, FormExtension, ModuleExtension, PagerFantaExtension, PriceExtension, ProgressBarExtension, TextExtension, TimeExtension
};

use Psr\Container\ContainerInterface;

use Framework\Middleware\{
    CsrfMiddleware
};

use function DI\{
    env,
    factory,
    get,
    object
};

return [
    'env' => env('ENV', 'dev'), //production

    'currency' => 'eur',
    PriceExtension::class => object()->constructor(get('currency')),

    'twig.extensions' => [
        get(RouterTwigExtension::class),
        get(PagerFantaExtension::class),
        get(TextExtension::class),
        get(TimeExtension::class),
        get(FlashExtension::class),
        get(FormExtension::class),
        get(CsrfExtension::class),
        get(ProgressBarExtension::class),
        get(ModuleExtension::class),
        get(PriceExtension::class)
    ],

    SessionInterface::class => object(PHPSession::class),
    CsrfMiddleware::class => object()->constructor(get(SessionInterface::class)),
    Router::class => factory(RouterFactory::class),

    // Renderer
    'views.path' => dirname(__DIR__) . '/views',
    RendererInterface::class => factory(TwigRendererFactory::class),

    // Database
    'database.host' => '127.0.0.1',
    'database.name' => 'blog',
    'database.user' => 'daivir',
    'database.pass' => 'virgile16',
    PDO::class => function (ContainerInterface $c) {
        return new PDO(
            "mysql:host={$c->get('database.host')};dbname={$c->get('database.name')}",
            $c->get('database.user'),
            $c->get('database.pass'),
            [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]
        );
    },

    // Mailer
    'mail.to' => 'admin@local.dev',
    'mail.from' => 'no-replay@admin.dev',
    Swift_Mailer::class => factory(\Framework\SwiftMailerFactory::class)
];
