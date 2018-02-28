<?php
namespace Tests\Framework\Modules;

class ModuleExceptionTest
{
	public function __construct(\Framework\Router $router)
	{
		$router->get('/moduletest', function () {
			return new \stdClass();
		}, 'moduletest');
	}
}
