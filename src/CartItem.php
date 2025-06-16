<?php

declare(strict_types=1);

namespace ShoppingCart;

use ShoppingCart\InvalidPriceException;
use ShoppingCart\InvalidQuantityException;

/**
 * Represents an item in the shopping cart
 */
class CartItem
{
    public function __construct(
        private readonly string $productId,
        private readonly float $unitPrice,
        private int $quantity
    ) {
        if ($unitPrice < 0) {
            throw new InvalidPriceException($unitPrice);
        }
        if ($quantity <= 0) {
            throw new InvalidQuantityException($quantity);
        }
    }

    public function getProductId(): string
    {
        return $this->productId;
    }

    public function getUnitPrice(): float
    {
        return $this->unitPrice;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new InvalidQuantityException($quantity);
        }
        $this->quantity = $quantity;
    }

    /**
     * Calculate the total price for this cart item
     */
    public function getTotal(): float
    {
        return $this->unitPrice * $this->quantity;
    }
}