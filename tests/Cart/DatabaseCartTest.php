<?php
namespace Tests\App\Cart;

use App\Cart\Cart;
use App\Cart\DatabaseCart;
use App\Cart\Table\CartRowTable;
use App\Cart\Table\CartTable;
use Tests\DatabaseTestCase;

class DatabaseCartTest extends DatabaseTestCase
{
    private $cartTable;
    private $cart;
    private $cartRowTable;

    public function setUp()
    {
        $pdo = $this->getPdo();
        $this->migrateDatabase($pdo);
        $this->seedDatabase($pdo);
        $this->cartTable = new CartTable($pdo);
        $this->cartRowTable = new CartRowTable($pdo);
        $this->cart = new DatabaseCart(2, $this->cartTable);
    }

    public function testAddProduct()
    {
        $products = $this->cartTable->getProductTable()->makeQuery()->limit(2)->fetchAll();
        $this->cart->addProduct($products[0]);
        $this->cart->addProduct($products[0]);
        $this->cart->addProduct($products[1], 2);
        self::assertEquals(2, $this->cartRowTable->count());
    }

    public function testPersistence()
    {
        $products = $this->cartTable->getProductTable()->makeQuery()->limit(2)->fetchAll();
        $this->cart->addProduct($products[0]);
        $this->cart->addProduct($products[1], 2);
        $cart = new DatabaseCart(2, $this->cartTable);
        $this->assertEquals(3, $cart->count());
    }

    public function testRemoveProduct()
    {
        $products = $this->cartTable->getProductTable()->makeQuery()->limit(2)->fetchAll();
        $this->cart->addProduct($products[0]);
        $this->cart->addProduct($products[1], 2);
        $this->cart->removeProduct($products[1]);
        self::assertEquals(1, $this->cartRowTable->count());
    }

    public function testMergeCart()
    {
        $products = $this->cartTable->getProductTable()->makeQuery()->limit(2)->fetchAll();
        $this->cart->addProduct($products[0]);

        $cart = new Cart();
        $cart->addProduct($products[0], 2);
        $this->cart->addProduct($products[1]);

        $this->cart->merge($cart);

        $this->assertEquals(4, $this->cart->count());
        $this->assertEquals(3, $this->cart->getRows()[0]->getQuantity());
        $this->assertEquals(1, $this->cart->getRows()[1]->getQuantity());
    }

    public function testEmptyCart()
    {
        $products = $this->cartTable->getProductTable()->makeQuery()->limit(2)->fetchAll();
        $this->cart->addProduct($products[0]);
        $this->cart->addProduct($products[0]);
        $this->cart->addProduct($products[1], 2);
        $this->cart->empty();
        self::assertEquals(0, $this->cartRowTable->count());
    }
}
