<?php

declare(strict_types=1);

namespace ShoppingCart\Tests;

use PHPUnit\Framework\TestCase;
use ShoppingCart\Cart;
use ShoppingCart\CartItem;
use ShoppingCart\BuyXGetYFree;
use ShoppingCart\InvalidDiscountException;

class BuyXGetYFreeTest extends TestCase
{
    public function testBuyXGetYFreeCreation(): void
    {
        $discount = new BuyXGetYFree(2, 1);
        $this->assertEquals(2, $discount->getBuyQuantity());
        $this->assertEquals(1, $discount->getFreeQuantity());
    }

    public function testSimpleBuy2Get1Free(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 10.00, 3)); // Buy 2, get 1 free
        
        $discount = new BuyXGetYFree(2, 1);
        $discountAmount = $discount->apply($cart);
        
        // 3 items: buy 2, get 1 free = $10.00 discount
        $this->assertEquals(10.0, $discountAmount);
    }

    public function testBuy3Get2Free(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 10.00, 5)); // Buy 3, get 2 free
        
        $discount = new BuyXGetYFree(3, 2);
        $discountAmount = $discount->apply($cart);
        
        // 5 items: one complete set (3+2=5) = 2 free items = $20.00 discount
        $this->assertEquals(20.0, $discountAmount);
    }

    public function testMultipleSets(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 10.00, 9)); // Buy 2, get 1 free
        
        $discount = new BuyXGetYFree(2, 1);
        $discountAmount = $discount->apply($cart);
        
        // 9 items: 3 complete sets (2+1=3 each) = 3 free items = $30.00 discount
        $this->assertEquals(30.0, $discountAmount);
    }

    public function testPartialSet(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 10.00, 4)); // Buy 2, get 1 free
        
        $discount = new BuyXGetYFree(2, 1);
        $discountAmount = $discount->apply($cart);
        
        // 4 items: 1 complete set (3 items) + 1 remaining
        // From complete set: 1 free item
        // From remaining: can't get free items (need at least 2 to buy)
        // Total: 1 free item = $10.00 discount
        $this->assertEquals(10.0, $discountAmount);
    }

    public function testPartialSetWithAdditionalFree(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 10.00, 8)); // Buy 3, get 2 free
        
        $discount = new BuyXGetYFree(3, 2);
        $discountAmount = $discount->apply($cart);
        
        // 8 items: 1 complete set (5 items) + 3 remaining
        // From complete set: 2 free items
        // From remaining 3: buy 3, can get up to 2 free but only have 0 left
        // So no additional free items from remaining
        // Total: 2 free items = $20.00 discount
        $this->assertEquals(20.0, $discountAmount);
    }

    public function testMultipleProducts(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 10.00, 3)); // Buy 2, get 1 free
        $cart->addItem(new CartItem('product-2', 20.00, 3)); // Buy 2, get 1 free
        
        $discount = new BuyXGetYFree(2, 1);
        $discountAmount = $discount->apply($cart);
        
        // Product 1: 1 free item = $10.00
        // Product 2: 1 free item = $20.00
        // Total: $30.00 discount
        $this->assertEquals(30.0, $discountAmount);
    }

    public function testInsufficientQuantity(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 10.00, 1)); // Need to buy 2
        
        $discount = new BuyXGetYFree(2, 1);
        $discountAmount = $discount->apply($cart);
        
        // Only 1 item, need 2 to qualify for discount
        $this->assertEquals(0.0, $discountAmount);
    }

    public function testEmptyCart(): void
    {
        $cart = new Cart();
        
        $discount = new BuyXGetYFree(2, 1);
        $discountAmount = $discount->apply($cart);
        
        $this->assertEquals(0.0, $discountAmount);
    }

    public function testZeroBuyQuantityThrowsException(): void
    {
        $this->expectException(InvalidDiscountException::class);
        $this->expectExceptionMessage('Invalid buy quantity: 0. Buy quantity must be positive.');
        new BuyXGetYFree(0, 1);
    }

    public function testNegativeBuyQuantityThrowsException(): void
    {
        $this->expectException(InvalidDiscountException::class);
        $this->expectExceptionMessage('Invalid buy quantity: -1. Buy quantity must be positive.');
        new BuyXGetYFree(-1, 1);
    }

    public function testZeroFreeQuantityThrowsException(): void
    {
        $this->expectException(InvalidDiscountException::class);
        $this->expectExceptionMessage('Invalid free quantity: 0. Free quantity must be positive.');
        new BuyXGetYFree(2, 0);
    }

    public function testNegativeFreeQuantityThrowsException(): void
    {
        $this->expectException(InvalidDiscountException::class);
        $this->expectExceptionMessage('Invalid free quantity: -1. Free quantity must be positive.');
        new BuyXGetYFree(2, -1);
    }

    public function testExactQuantityForDiscount(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 10.00, 2)); // Exactly buy 2
        
        $discount = new BuyXGetYFree(2, 1);
        $discountAmount = $discount->apply($cart);
        
        // Have exactly 2 items, qualify to buy 2 but no free items available
        $this->assertEquals(0.0, $discountAmount);
    }

    public function testComplexScenario(): void
    {
        $cart = new Cart();
        // Buy 3 get 1 free, with 7 items
        $cart->addItem(new CartItem('product-1', 15.00, 7));
        
        $discount = new BuyXGetYFree(3, 1);
        $discountAmount = $discount->apply($cart);
        
        // 7 items: 1 complete set (4 items) + 3 remaining
        // From complete set: 1 free item
        // From remaining 3: buy 3, get 1 free but no items left for free
        // Total: 1 free item = $15.00 discount
        $this->assertEquals(15.0, $discountAmount);
    }
}