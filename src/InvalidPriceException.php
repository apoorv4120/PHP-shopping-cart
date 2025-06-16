<?php

declare(strict_types=1);

namespace ShoppingCart;

class InvalidPriceException extends CartException
{
    public function __construct(float $price)
    {
        parent::__construct(sprintf('Invalid price: %.2f. Price must be non-negative.', $price));
    }
} 