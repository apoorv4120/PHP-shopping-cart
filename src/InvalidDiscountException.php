<?php

declare(strict_types=1);

namespace ShoppingCart;

class InvalidDiscountException extends CartException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
} 