# Shopping Cart with Pluggable Discount Strategies

A PHP e-commerce shopping cart implementation featuring pluggable discount strategies and a simple web interface.

## Features

- **Object-oriented design** with proper separation of concerns
- **Pluggable discount strategies** using the Strategy pattern
- **Comprehensive test coverage** with PHPUnit
- **Simple web UI** for demonstration
- **PSR-4 autoloading** and **PSR-12 coding standards**
- **PHP 8.0+ strict typing**

## Installation

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   ```

## Running Tests

Execute the PHPUnit test suite:
```bash
vendor/bin/phpunit
```

For detailed output with coverage information:
```bash
vendor/bin/phpunit --coverage-text
```

## Starting the Web UI

Start the built-in PHP development server:
```bash
php -S localhost:8000 -t public
```

Then open your browser to: http://localhost:8000/

## Project Structure

```
├── composer.json          # Composer configuration
├── composer.lock          # Composer lock file
├── phpunit.xml            # PHPUnit configuration
├── README.md              # This file
├── src/                   # Source code
│   ├── Cart.php           # Main cart class
│   ├── CartItem.php       # Cart item entity
│   ├── DiscountStrategyInterface.php  # Strategy interface
│   ├── PercentageDiscount.php        # Percentage discount strategy
│   └── BuyXGetYFree.php             # Buy X Get Y Free strategy
├── tests/                 # PHPUnit tests
│   ├── CartTest.php
│   ├── CartItemTest.php
│   ├── PercentageDiscountTest.php
│   └── BuyXGetYFreeTest.php
└── public/
    └── index.php          # Web interface
```

## Core Classes

### CartItem
Represents a single item in the cart with:
- Product ID (string)
- Unit price (float)
- Quantity (int)
- Validation for positive quantities and non-negative prices

### Cart
Main shopping cart that:
- Holds CartItem instances
- Manages adding/removing items
- Calculates subtotals and totals
- Accepts discount strategies at runtime
- Handles item quantity updates for duplicate products

### Discount Strategies

#### DiscountStrategyInterface
Defines the contract for all discount strategies with a single `apply(Cart $cart): float` method.

#### PercentageDiscount
Applies a percentage discount (0-100%) to the entire cart subtotal.

#### BuyXGetYFree
Implements "Buy X, Get Y Free" logic:
- Applies to each product individually
- Handles multiple sets per product
- Calculates partial sets correctly
- Example: Buy 2 Get 1 Free with 5 items = 1 complete set (3 items) + 2 remaining = 1 free item

## Design Notes

### Architecture & SOLID Principles

**Single Responsibility Principle**: Each class has a single, well-defined purpose:
- `CartItem` handles individual item data and calculations
- `Cart` manages the collection of items and applies discounts
- Each discount strategy implements one specific discount type

**Open/Closed Principle**: The system is open for extension but closed for modification. New discount strategies can be added by implementing `DiscountStrategyInterface` without changing existing code.

**Liskov Substitution Principle**: All discount strategies are interchangeable through the common interface, allowing runtime strategy switching.

**Interface Segregation Principle**: The `DiscountStrategyInterface` is minimal and focused, containing only the essential `apply()` method.

**Dependency Inversion Principle**: The `Cart` class depends on the `DiscountStrategyInterface` abstraction, not concrete implementations.

### Strategy Pattern Implementation

The discount system uses the Strategy pattern to enable:
- **Runtime strategy selection**: Change discount types without restarting
- **Easy extensibility**: Add new discount types by implementing the interface
- **Clean separation**: Discount logic is isolated from cart management
- **Testability**: Each strategy can be tested independently

### Extensibility

Adding new discount strategies is straightforward:

1. **Create a new class** implementing `DiscountStrategyInterface`
2. **Implement the `apply()` method** with your discount logic
3. **Add tests** for the new strategy
4. **Update the UI** to include the new option (optional)

Example strategies that could be easily added:
- Fixed amount discount ($10 off)
- Tiered discounts (spend $100, get 10% off)
- Product-specific discounts
- Quantity-based discounts
- Time-based discounts (happy hour, seasonal)
- Customer loyalty discounts

### Error Handling

The system includes comprehensive validation:
- **CartItem validation**: Prevents negative prices and non-positive quantities
- **Discount validation**: Ensures percentage discounts are within 0-100% range
- **Cart operations**: Handles edge cases like empty carts and non-existent items
- **Web UI**: Displays user-friendly error messages for invalid inputs

### Session Management

The web interface uses PHP sessions to persist cart data between requests, providing a realistic shopping experience without requiring a database.

## Testing

The test suite includes:
- **Unit tests** for all classes and methods
- **Edge case testing** (empty carts, invalid inputs, boundary conditions)
- **Integration tests** for discount application
- **Exception testing** for validation logic
- **Multiple scenario testing** for complex discount calculations

Test coverage includes:
- Cart operations (add, remove, clear)
- Item validation and calculations
- Discount strategy implementations
- End-to-end total calculations
- Error conditions and edge cases

## Web Interface Features

- **Add items** with product ID, price, and quantity
- **Remove individual items** or clear entire cart
- **Apply discount strategies** with dynamic configuration
- **Real-time calculations** showing subtotal, discount, and total
- **Responsive design** using Bootstrap 5
- **Session persistence** maintaining cart between page reloads
- **User-friendly error handling** with success/error messages