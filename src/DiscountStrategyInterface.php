<?php

declare(strict_types=1);

namespace ShoppingCart;

/**
 * Interface for discount strategies following Strategy pattern
 */
interface DiscountStrategyInterface
{
    /**
     * Apply discount to cart and return discount amount
     * 
     * @param Cart $cart The cart to apply discount to
     * @return float The discount amount (positive value)
     */
    public function apply(Cart $cart): float;
} 