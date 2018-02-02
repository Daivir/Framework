<?php
namespace Tests\App\Cart\Table;

use App\Cart\Cart;
use App\Cart\Entity\Order;
use App\Cart\Table\OrderRowTable;
use App\Cart\Table\OrderTable;
use App\Shop\Table\ProductTable;
use Tests\DatabaseTestCase;

class OrderTableTest extends DatabaseTestCase
{
    private $orderTable;

    private $orderRowTable;

    private $productTable;

    public function setUp()
    {
        $pdo = $this->getPdo();
        $this->migrateDatabase($pdo);
        $this->seedDatabase($pdo);
        $this->orderTable = new OrderTable($pdo);
        $this->productTable = new ProductTable($pdo);
        $this->orderRowTable = new OrderRowTable($pdo);
    }

    public function testCreateFromCart()
    {
        $products = $this->productTable->makeQuery()->limit(10)->fetchAll();
        $cart = new Cart;
        $cart->addProduct($products[0]);
        $cart->addProduct($products[1], 2);
        $this->orderTable->createFromCart($cart, [
            'country' => 'fr',
            'vat' => 0,
            'user_id' => 1
        ]);
        /** @var Order $order */
        $order = $this->orderTable->find(1);
        $this->assertEquals($cart->getPrice(), $order->getPrice());
        $this->assertEquals(2, $this->orderRowTable->count());
        return $order;
    }

    public function testFindRows()
    {
        $order = $this->testCreateFromCart();
        $this->orderTable->findRows([$order]);
        $this->assertCount(2, $order->getRows());
    }
}
