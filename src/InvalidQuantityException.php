<?php

declare(strict_types=1);

namespace ShoppingCart;

class InvalidQuantityException extends CartException
{
    public function __construct(int $quantity)
    {
        parent::__construct(sprintf('Invalid quantity: %d. Quantity must be positive.', $quantity));
    }
} 