<?php

declare(strict_types=1);

namespace ShoppingCart;

use ShoppingCart\InvalidDiscountException;

/**
 * Buy X items, get Y items free discount strategy
 * Applies to each product individually
 */
class BuyXGetYFree implements DiscountStrategyInterface
{
    public function __construct(
        private readonly int $buyQuantity,
        private readonly int $freeQuantity
    ) {
        if ($buyQuantity <= 0) {
            throw new InvalidDiscountException(
                sprintf('Invalid buy quantity: %d. Buy quantity must be positive.', $buyQuantity)
            );
        }
        if ($freeQuantity <= 0) {
            throw new InvalidDiscountException(
                sprintf('Invalid free quantity: %d. Free quantity must be positive.', $freeQuantity)
            );
        }
    }

    public function getBuyQuantity(): int
    {
        return $this->buyQuantity;
    }

    public function getFreeQuantity(): int
    {
        return $this->freeQuantity;
    }

    public function apply(Cart $cart): float
    {
        $totalDiscount = 0.0;

        foreach ($cart->getItems() as $item) {
            $quantity = $item->getQuantity();
            $unitPrice = $item->getUnitPrice();
            
            // Calculate how many complete "buy X get Y free" sets we have
            $sets = intval($quantity / ($this->buyQuantity + $this->freeQuantity));
            
            // Calculate remaining items after complete sets
            $remaining = $quantity % ($this->buyQuantity + $this->freeQuantity);
            
            // For remaining items, see if we can get some free items
            $additionalFreeItems = 0;
            if ($remaining >= $this->buyQuantity) {
                $additionalFreeItems = min(
                    $this->freeQuantity,
                    $remaining - $this->buyQuantity
                );
            }
            
            // Total free items = free items from complete sets + additional free items
            $totalFreeItems = ($sets * $this->freeQuantity) + $additionalFreeItems;
            
            // Discount is the value of free items
            $totalDiscount += $totalFreeItems * $unitPrice;
        }

        return $totalDiscount;
    }
}