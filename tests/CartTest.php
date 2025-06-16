<?php

declare(strict_types=1);

namespace ShoppingCart\Tests;

use PHPUnit\Framework\TestCase;
use ShoppingCart\Cart;
use ShoppingCart\CartItem;
use ShoppingCart\PercentageDiscount;
use ShoppingCart\BuyXGetYFree;

class CartTest extends TestCase
{
    private Cart $cart;

    protected function setUp(): void
    {
        $this->cart = new Cart();
    }

    public function testEmptyCart(): void
    {
        $this->assertTrue($this->cart->isEmpty());
        $this->assertEquals(0, $this->cart->getItemCount());
        $this->assertEquals(0.0, $this->cart->getSubtotal());
        $this->assertEquals(0.0, $this->cart->getTotal());
        $this->assertEmpty($this->cart->getItems());
    }

    public function testAddItem(): void
    {
        $item = new CartItem('product-1', 10.00, 2);
        $this->cart->addItem($item);

        $this->assertFalse($this->cart->isEmpty());
        $this->assertEquals(2, $this->cart->getItemCount());
        $this->assertEquals(20.00, $this->cart->getSubtotal());
        $this->assertCount(1, $this->cart->getItems());
    }

    public function testAddSameItemTwice(): void
    {
        $item1 = new CartItem('product-1', 10.00, 2);
        $item2 = new CartItem('product-1', 10.00, 3);
        
        $this->cart->addItem($item1);
        $this->cart->addItem($item2);

        $this->assertEquals(5, $this->cart->getItemCount()); // 2 + 3
        $this->assertEquals(50.00, $this->cart->getSubtotal()); // 5 * 10.00
        $this->assertCount(1, $this->cart->getItems()); // Only one unique product
    }

    public function testAddDifferentItems(): void
    {
        $item1 = new CartItem('product-1', 10.00, 2);
        $item2 = new CartItem('product-2', 15.00, 1);
        
        $this->cart->addItem($item1);
        $this->cart->addItem($item2);

        $this->assertEquals(3, $this->cart->getItemCount()); // 2 + 1
        $this->assertEquals(35.00, $this->cart->getSubtotal()); // 20.00 + 15.00
        $this->assertCount(2, $this->cart->getItems());
    }

    public function testRemoveItem(): void
    {
        $item = new CartItem('product-1', 10.00, 2);
        $this->cart->addItem($item);
        
        $this->assertTrue($this->cart->removeItem('product-1'));
        $this->assertTrue($this->cart->isEmpty());
        
        // Try to remove non-existent item
        $this->assertFalse($this->cart->removeItem('product-2'));
    }

    public function testGetItem(): void
    {
        $item = new CartItem('product-1', 10.00, 2);
        $this->cart->addItem($item);
        
        $retrievedItem = $this->cart->getItem('product-1');
        $this->assertNotNull($retrievedItem);
        $this->assertEquals('product-1', $retrievedItem->getProductId());
        
        $this->assertNull($this->cart->getItem('non-existent'));
    }

    public function testClear(): void
    {
        $item1 = new CartItem('product-1', 10.00, 2);
        $item2 = new CartItem('product-2', 15.00, 1);
        
        $this->cart->addItem($item1);
        $this->cart->addItem($item2);
        
        $this->assertFalse($this->cart->isEmpty());
        
        $this->cart->clear();
        
        $this->assertTrue($this->cart->isEmpty());
        $this->assertEquals(0, $this->cart->getItemCount());
    }

    public function testDiscountStrategy(): void
    {
        $strategy = new PercentageDiscount(10.0);
        $this->cart->setDiscountStrategy($strategy);
        
        $this->assertSame($strategy, $this->cart->getDiscountStrategy());
    }

    public function testGetTotalWithPercentageDiscount(): void
    {
        $item = new CartItem('product-1', 100.00, 1);
        $this->cart->addItem($item);
        
        $this->cart->setDiscountStrategy(new PercentageDiscount(20.0));
        
        $this->assertEquals(100.00, $this->cart->getSubtotal());
        $this->assertEquals(20.00, $this->cart->getDiscountAmount());
        $this->assertEquals(80.00, $this->cart->getTotal());
    }

    public function testGetTotalWithBuyXGetYFree(): void
    {
        $item = new CartItem('product-1', 10.00, 5); // Buy 3, get 2 free
        $this->cart->addItem($item);
        
        $this->cart->setDiscountStrategy(new BuyXGetYFree(3, 2));
        
        $this->assertEquals(50.00, $this->cart->getSubtotal());
        $this->assertEquals(20.00, $this->cart->getDiscountAmount()); // 2 free items * $10
        $this->assertEquals(30.00, $this->cart->getTotal());
    }

    public function testGetTotalWithoutDiscount(): void
    {
        $item = new CartItem('product-1', 10.00, 5);
        $this->cart->addItem($item);
        
        $this->assertEquals(50.00, $this->cart->getSubtotal());
        $this->assertEquals(0.00, $this->cart->getDiscountAmount());
        $this->assertEquals(50.00, $this->cart->getTotal());
    }

    public function testDiscountOnEmptyCart(): void
    {
        $this->cart->setDiscountStrategy(new PercentageDiscount(50.0));
        
        $this->assertEquals(0.0, $this->cart->getDiscountAmount());
        $this->assertEquals(0.0, $this->cart->getTotal());
    }
}