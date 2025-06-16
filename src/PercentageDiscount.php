<?php

declare(strict_types=1);

namespace ShoppingCart;

use ShoppingCart\InvalidDiscountException;

/**
 * Applies a percentage discount to the entire cart
 */
class PercentageDiscount implements DiscountStrategyInterface
{
    public function __construct(private readonly float $percentage)
    {
        if ($percentage < 0 || $percentage > 100) {
            throw new InvalidDiscountException(
                sprintf('Invalid percentage: %.2f. Percentage must be between 0 and 100.', $percentage)
            );
        }
    }

    public function getPercentage(): float
    {
        return $this->percentage;
    }

    public function apply(Cart $cart): float
    {
        $subtotal = $cart->getSubtotal();
        return $subtotal * ($this->percentage / 100);
    }
}