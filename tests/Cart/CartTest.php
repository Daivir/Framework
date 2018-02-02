<?php
namespace Tests\App\Cart;

use App\Cart\Cart;
use App\Shop\Entity\Product;
use PHPUnit\Framework\TestCase;

class CartTest extends TestCase
{
    private $cart;

    public function setUp()
    {
        $this->cart = new Cart();
    }

    public function testAddProduct()
    {
        $product1 = new Product;
        $product1->setId(1);
        $product2 = new Product;
        $product2->setId(2);
        $this->cart->addProduct($product1);
        $this->assertEquals(1, $this->cart->count());
        $this->assertCount(1, $this->cart->getRows());
        $this->cart->addProduct($product2);
        $this->assertEquals(2, $this->cart->count());
        $this->assertCount(2, $this->cart->getRows());
        $this->cart->addProduct($product1);
        $this->assertEquals(3, $this->cart->count());
        $this->assertCount(2, $this->cart->getRows());
    }

    public function testRemoveProduct()
    {
        $product1 = new Product;
        $product1->setId(1);
        $product2 = new Product;
        $product2->setId(2);
        $this->cart->addProduct($product1);
        $this->cart->addProduct($product2);
        $this->assertEquals(2, $this->cart->count());
        $this->cart->removeProduct($product1);
        $this->assertEquals(1, $this->cart->count());
    }

    public function testAddProductWithQuantity()
    {
        $product1 = new Product;
        $product1->setId(1);
        $product2 = new Product;
        $product2->setId(2);
        $this->cart->addProduct($product1, 3);
        $this->cart->addProduct($product2, 2);
        $this->assertEquals(5, $this->cart->count());
        $this->assertCount(2, $this->cart->getRows());
    }
}
