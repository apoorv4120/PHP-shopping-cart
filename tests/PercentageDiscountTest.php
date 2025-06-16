<?php

declare(strict_types=1);

namespace ShoppingCart\Tests;

use PHPUnit\Framework\TestCase;
use ShoppingCart\Cart;
use ShoppingCart\CartItem;
use ShoppingCart\PercentageDiscount;
use ShoppingCart\InvalidDiscountException;

class PercentageDiscountTest extends TestCase
{
    public function testPercentageDiscountCreation(): void
    {
        $discount = new PercentageDiscount(15.5);
        $this->assertEquals(15.5, $discount->getPercentage());
    }

    public function testApplyPercentageDiscount(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 100.00, 1));
        $cart->addItem(new CartItem('product-2', 50.00, 2));
        
        $discount = new PercentageDiscount(20.0);
        $discountAmount = $discount->apply($cart);
        
        // Subtotal: 100 + (50 * 2) = 200
        // 20% discount: 200 * 0.20 = 40
        $this->assertEquals(40.0, $discountAmount);
    }

    public function testApplyZeroPercentageDiscount(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 100.00, 1));
        
        $discount = new PercentageDiscount(0.0);
        $discountAmount = $discount->apply($cart);
        
        $this->assertEquals(0.0, $discountAmount);
    }

    public function testApplyFullPercentageDiscount(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 100.00, 1));
        
        $discount = new PercentageDiscount(100.0);
        $discountAmount = $discount->apply($cart);
        
        $this->assertEquals(100.0, $discountAmount);
    }

    public function testApplyPercentageDiscountToEmptyCart(): void
    {
        $cart = new Cart();
        
        $discount = new PercentageDiscount(50.0);
        $discountAmount = $discount->apply($cart);
        
        $this->assertEquals(0.0, $discountAmount);
    }

    public function testNegativePercentageThrowsException(): void
    {
        $this->expectException(InvalidDiscountException::class);
        $this->expectExceptionMessage('Invalid percentage: -5.00. Percentage must be between 0 and 100.');
        new PercentageDiscount(-5.0);
    }

    public function testPercentageOver100ThrowsException(): void
    {
        $this->expectException(InvalidDiscountException::class);
        $this->expectExceptionMessage('Invalid percentage: 105.00. Percentage must be between 0 and 100.');
        new PercentageDiscount(105.0);
    }

    public function testDecimalPercentage(): void
    {
        $cart = new Cart();
        $cart->addItem(new CartItem('product-1', 100.00, 1));
        
        $discount = new PercentageDiscount(12.5); // 12.5%
        $discountAmount = $discount->apply($cart);
        
        $this->assertEquals(12.5, $discountAmount);
    }
}