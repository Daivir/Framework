<?php
namespace App\Auth;

use Virton\Auth;


class AuthTwigExtension extends \Twig_Extension
{
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('current_user', [$this->auth, 'getUser'])
        ];
    }
}
