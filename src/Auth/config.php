<?php

use App\Auth\{
	DatabaseAuth, ForbiddenMiddleware, Mailer\ResetPasswordMailer, NoRecordMiddleware, UserTable
};
use Virton\Auth;
use function DI\{
    add,
    object,
    get
};
use App\Auth\AuthTwigExtension;

return [
    'auth.login' => '/login',
    'auth.logout' => '/logout',
    'auth.password' => '/password',
    'auth.reset' => '/password/reset/{id:\d+}/{token}',

    'auth.entity' => \App\Auth\User::class,
    'twig.extensions' => add([
        get(AuthTwigExtension::class)
    ]),
    Auth::class => get(DatabaseAuth::class),
    UserTable::class => object()->constructorParameter('entity', get('auth.entity')),
    ForbiddenMiddleware::class => object()->constructorParameter('loginPath', get('auth.login')),
	NoRecordMiddleware::class => object()->constructorParameter('loginPath', get('auth.login')),
    ResetPasswordMailer::class => object()->constructorParameter('from', get('mail.from'))
];
