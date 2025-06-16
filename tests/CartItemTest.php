<?php

declare(strict_types=1);

namespace ShoppingCart\Tests;

use PHPUnit\Framework\TestCase;
use ShoppingCart\CartItem;
use ShoppingCart\InvalidPriceException;
use ShoppingCart\InvalidQuantityException;

class CartItemTest extends TestCase
{
    public function testCartItemCreation(): void
    {
        $item = new CartItem('product-1', 10.50, 2);
        
        $this->assertEquals('product-1', $item->getProductId());
        $this->assertEquals(10.50, $item->getUnitPrice());
        $this->assertEquals(2, $item->getQuantity());
    }

    public function testGetTotal(): void
    {
        $item = new CartItem('product-1', 10.50, 3);
        $this->assertEquals(31.50, $item->getTotal());
    }

    public function testSetQuantity(): void
    {
        $item = new CartItem('product-1', 10.00, 1);
        $item->setQuantity(5);
        $this->assertEquals(5, $item->getQuantity());
        $this->assertEquals(50.00, $item->getTotal());
    }

    public function testNegativeUnitPriceThrowsException(): void
    {
        $this->expectException(InvalidPriceException::class);
        $this->expectExceptionMessage('Invalid price: -5.00. Price must be non-negative.');
        new CartItem('product-1', -5.00, 1);
    }

    public function testZeroQuantityThrowsException(): void
    {
        $this->expectException(InvalidQuantityException::class);
        $this->expectExceptionMessage('Invalid quantity: 0. Quantity must be positive.');
        new CartItem('product-1', 10.00, 0);
    }

    public function testNegativeQuantityThrowsException(): void
    {
        $this->expectException(InvalidQuantityException::class);
        $this->expectExceptionMessage('Invalid quantity: -1. Quantity must be positive.');
        new CartItem('product-1', 10.00, -1);
    }

    public function testSetQuantityZeroThrowsException(): void
    {
        $item = new CartItem('product-1', 10.00, 1);
        
        $this->expectException(InvalidQuantityException::class);
        $this->expectExceptionMessage('Invalid quantity: 0. Quantity must be positive.');
        $item->setQuantity(0);
    }

    public function testSetQuantityNegativeThrowsException(): void
    {
        $item = new CartItem('product-1', 10.00, 1);
        
        $this->expectException(InvalidQuantityException::class);
        $this->expectExceptionMessage('Invalid quantity: -1. Quantity must be positive.');
        $item->setQuantity(-1);
    }
}