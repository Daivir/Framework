<?php

namespace Tests\Framework\Actions;

use Virton\Actions\CrudAction;
use Virton\Database\Query;
use Virton\Database\Table;
use Virton\Renderer\RendererInterface;
use Virton\Router;
use Virton\Session\FlashHandler;
use GuzzleHttp\Psr7\ServerRequest;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;
use PHPUnit\Framework\TestCase;

class CrudActionTest extends TestCase
{
    private $query;
    private $table;
    private $flash;
    private $renderer;

    public function setUp()
    {
        $this->table = $this->getMockBuilder(Table::class)->disableOriginalConstructor()->getMock();
        $this->query = $this->getMockBuilder(Query::class)->getMock();
        $this->table->method('getEntity')->willReturn(\stdClass::class);
        $this->table->method('findAll')->willReturn($this->query);
        $this->table->method('find')->willReturnCallback(function ($id) {
            $object = new \stdClass();
            $object->id = (int)$id;
            return $object;
        });
        $this->flash = $this->getMockBuilder(FlashHandler::class)->disableOriginalConstructor()->getMock();
        $this->renderer = $this->getMockBuilder(RendererInterface::class)->getMock();
    }

    private function makeCrudAction()
    {
        $this->renderer->method('render')->willReturn('');
        $router = $this->getMockBuilder(Router::class)->getMock();
        $router->method('generateUri')->willReturnCallback(function ($url) {
            return $url;
        });
        $action = new CrudAction($this->renderer, $router, $this->table, $this->flash);
        $property = (new \ReflectionClass($action))->getProperty('viewPath');
        $property->setAccessible(true);
        $property->setValue($action, '@test');
        $property = (new \ReflectionClass($action))->getProperty('acceptedParams');
        $property->setAccessible(true);
        $property->setValue($action, ['name']);
        return $action;
    }

    public function testIndex()
    {
        $request = new ServerRequest('GET', '/test');
        $pager = new Pagerfanta(new ArrayAdapter([1, 2]));
        $this->query->method('paginate')->willReturn($pager);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with('@test/index', ['items' => $pager])
        ;
        call_user_func($this->makeCrudAction(), $request);
    }

    public function testEdit()
    {
        $id = 3;
        $request = (new ServerRequest('GET', '/test'))->withAttribute('id', $id);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with('@test/edit', $this->callback(function ($params) use ($id) {
                $this->assertAttributeEquals($id, 'id', $params['item']);
                return true;
            }));
        ;
        call_user_func($this->makeCrudAction(), $request);
    }

    public function testEditWithParams()
    {
        $id = 3;
        $request = (new ServerRequest('POST', '/demo'))
            ->withAttribute('id', $id)
            ->withParsedBody(['name' => 'demo']);
        $this->table
            ->expects($this->once())
            ->method('update')
            ->with($id, ['name' => 'demo']);
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = call_user_func($this->makeCrudAction(), $request);
        $this->assertEquals(['.index'], $response->getHeader('Location'));
    }

    public function testDelete()
    {
        $id = 3;
        $request = (new ServerRequest('DELETE', '/test'))
            ->withAttribute('id', $id);
        $this->table
            ->expects($this->once())
            ->method('delete')
            ->with($id);
        $response = call_user_func($this->makeCrudAction(), $request);
        $this->assertEquals(['.index'], $response->getHeader('Location'));
    }

    public function testCreate()
    {
        $id = 3;
        $request = (new ServerRequest('GET', '/new'))->withAttribute('id', $id);
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with('@test/create', $this->callback(function ($params) use ($id) {
                $this->assertInstanceOf(\stdClass::class, $params['item']);
                return true;
            }));
        call_user_func($this->makeCrudAction(), $request);
    }

    public function testCreateWithParams()
    {
        $request = (new ServerRequest('POST', '/new'))
            ->withParsedBody(['name' => 'Test']);
        $this->table
            ->expects($this->once())
            ->method('insert')
            ->with(['name' => 'Test']);
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = call_user_func($this->makeCrudAction(), $request);
        $this->assertEquals(['.index'], $response->getHeader('Location'));
    }
}
