<?php

declare(strict_types=1);

namespace ShoppingCart;

/**
 * Shopping cart that holds items and applies discount strategies
 */
class Cart
{
    /** @var CartItem[] */
    private array $items = [];
    private ?DiscountStrategyInterface $discountStrategy = null;

    /**
     * Add item to cart or update quantity if item already exists
     */
    public function addItem(CartItem $item): void
    {
        $productId = $item->getProductId();
        
        if (isset($this->items[$productId])) {
            // Item already exists, update quantity
            $existingItem = $this->items[$productId];
            $newQuantity = $existingItem->getQuantity() + $item->getQuantity();
            $existingItem->setQuantity($newQuantity);
        } else {
            // New item
            $this->items[$productId] = $item;
        }
    }

    /**
     * Remove item from cart by product ID
     */
    public function removeItem(string $productId): bool
    {
        if (isset($this->items[$productId])) {
            unset($this->items[$productId]);
            return true;
        }
        return false;
    }

    /**
     * Get all items in the cart
     * 
     * @return CartItem[]
     */
    public function getItems(): array
    {
        return array_values($this->items);
    }

    /**
     * Get item by product ID
     */
    public function getItem(string $productId): ?CartItem
    {
        return $this->items[$productId] ?? null;
    }

    /**
     * Check if cart is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * Get total number of items in cart
     */
    public function getItemCount(): int
    {
        return array_sum(array_map(
            fn(CartItem $item) => $item->getQuantity(),
            $this->items
        ));
    }

    /**
     * Calculate subtotal (before discount)
     */
    public function getSubtotal(): float
    {
        return array_sum(array_map(
            fn(CartItem $item) => $item->getTotal(),
            $this->items
        ));
    }

    /**
     * Set discount strategy
     */
    public function setDiscountStrategy(?DiscountStrategyInterface $strategy): void
    {
        $this->discountStrategy = $strategy;
    }

    /**
     * Get current discount strategy
     */
    public function getDiscountStrategy(): ?DiscountStrategyInterface
    {
        return $this->discountStrategy;
    }

    /**
     * Calculate discount amount
     */
    public function getDiscountAmount(): float
    {
        if ($this->discountStrategy === null || $this->isEmpty()) {
            return 0.0;
        }

        return $this->discountStrategy->apply($this);
    }

    /**
     * Calculate final total (subtotal - discount)
     */
    public function getTotal(): float
    {
        $subtotal = $this->getSubtotal();
        $discount = $this->getDiscountAmount();
        
        return max(0.0, $subtotal - $discount);
    }

    /**
     * Clear all items from cart
     */
    public function clear(): void
    {
        $this->items = [];
    }
}