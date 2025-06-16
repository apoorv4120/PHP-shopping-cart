<?php

declare(strict_types=1);

// Autoload classes
require_once __DIR__ . '/../vendor/autoload.php';

// Start session to persist cart data
session_start();

use ShoppingCart\Cart;
use ShoppingCart\CartItem;
use ShoppingCart\PercentageDiscount;
use ShoppingCart\BuyXGetYFree;
use ShoppingCart\CartException;

// Initialize cart from session or create new one
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = new Cart();
}
$cart = $_SESSION['cart'];

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add_item':
                    $productId = trim($_POST['product_id'] ?? '');
                    $unitPrice = (float)($_POST['unit_price'] ?? 0);
                    $quantity = (int)($_POST['quantity'] ?? 0);
                    
                    if (empty($productId)) {
                        throw new InvalidArgumentException('Product ID is required');
                    }
                    
                    $item = new CartItem($productId, $unitPrice, $quantity);
                    $cart->addItem($item);
                    $message = "Added {$quantity} x {$productId} to cart";
                    break;
                    
                case 'remove_item':
                    $productId = $_POST['product_id'] ?? '';
                    if ($cart->removeItem($productId)) {
                        $message = "Removed {$productId} from cart";
                    } else {
                        $error = "Item not found in cart";
                    }
                    break;
                    
                case 'clear_cart':
                    $cart->clear();
                    $message = "Cart cleared";
                    break;
                    
                case 'apply_discount':
                    $discountType = $_POST['discount_type'] ?? 'none';
                    
                    switch ($discountType) {
                        case 'none':
                            $cart->setDiscountStrategy(null);
                            $message = "Discount removed";
                            break;
                            
                        case 'percentage':
                            $percentage = (float)($_POST['percentage'] ?? 0);
                            $cart->setDiscountStrategy(new PercentageDiscount($percentage));
                            $message = "Applied {$percentage}% discount";
                            break;
                            
                        case 'buy_x_get_y':
                            $buyX = (int)($_POST['buy_x'] ?? 0);
                            $getY = (int)($_POST['get_y'] ?? 0);
                            $cart->setDiscountStrategy(new BuyXGetYFree($buyX, $getY));
                            $message = "Applied Buy {$buyX} Get {$getY} Free discount";
                            break;
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
    
    // Save cart back to session
    $_SESSION['cart'] = $cart;
}

// Get current discount info for display
$currentDiscount = $cart->getDiscountStrategy();
$discountInfo = '';
if ($currentDiscount instanceof PercentageDiscount) {
    $discountInfo = $currentDiscount->getPercentage() . '% off';
} elseif ($currentDiscount instanceof BuyXGetYFree) {
    $discountInfo = 'Buy ' . $currentDiscount->getBuyQuantity() . ' Get ' . $currentDiscount->getFreeQuantity() . ' Free';
} else {
    $discountInfo = 'No discount';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .discount-config { display: none; }
        .discount-config.active { display: block; }
        .cart-summary { background-color: #f8f9fa; }
        #notification-container {
            position: fixed;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            z-index: 9999;
            min-width: 300px;
            max-width: 90vw;
            text-align: center;
            pointer-events: none;
        }
        #notification-message {
            pointer-events: auto;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1>Shopping Cart</h1>
        
        <!-- Notification Message (moved to bottom) -->
        <div id="notification-container">
            <?php if ($message): ?>
                <div class="alert alert-success" id="notification-message"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-danger" id="notification-message"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Add Item Form -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Add Item</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_item">
                            
                            <div class="mb-3">
                                <label for="product_id" class="form-label">Product ID</label>
                                <input type="text" class="form-control" id="product_id" name="product_id" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="unit_price" class="form-label">Unit Price ($)</label>
                                <input type="number" class="form-control" id="unit_price" name="unit_price" 
                                       step="0.01" min="0" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" 
                                       min="1" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Add to Cart</button>
                        </form>
                    </div>
                </div>

                <!-- Discount Configuration -->
                <div class="card">
                    <div class="card-header">
                        <h5>Discount</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="apply_discount">
                            
                            <div class="mb-3">
                                <label for="discount_type" class="form-label">Discount Type</label>
                                <select class="form-select" id="discount_type" name="discount_type" onchange="toggleDiscountConfig()">
                                    <option value="none">No Discount</option>
                                    <option value="percentage">Percentage Discount</option>
                                    <option value="buy_x_get_y">Buy X Get Y Free</option>
                                </select>
                            </div>
                            
                            <div id="percentage_config" class="discount-config mb-3">
                                <label for="percentage" class="form-label">Percentage (%)</label>
                                <input type="number" class="form-control" id="percentage" name="percentage" 
                                       step="0.1" min="0" max="100">
                            </div>
                            
                            <div id="buy_x_get_y_config" class="discount-config mb-3">
                                <div class="row">
                                    <div class="col">
                                        <label for="buy_x" class="form-label">Buy X</label>
                                        <input type="number" class="form-control" id="buy_x" name="buy_x" min="1">
                                    </div>
                                    <div class="col">
                                        <label for="get_y" class="form-label">Get Y Free</label>
                                        <input type="number" class="form-control" id="get_y" name="get_y" min="1">
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-success">Apply Discount</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cart Display -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Cart</h5>
                        <?php if (!$cart->isEmpty()): ?>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="clear_cart">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Clear Cart</button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if ($cart->isEmpty()): ?>
                            <p class="text-muted">Your cart is empty</p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Qty</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cart->getItems() as $item): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item->getProductId()) ?></td>
                                                <td>$<?= number_format($item->getUnitPrice(), 2) ?></td>
                                                <td><?= $item->getQuantity() ?></td>
                                                <td>$<?= number_format($item->getTotal(), 2) ?></td>
                                                <td>
                                                    <form method="POST" style="margin: 0;">
                                                        <input type="hidden" name="action" value="remove_item">
                                                        <input type="hidden" name="product_id" value="<?= htmlspecialchars($item->getProductId()) ?>">
                                                        <button type="submit" class="btn btn-outline-danger btn-sm">×</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="cart-summary p-3 mt-3">
                                <div class="row">
                                    <div class="col">
                                        <strong>Subtotal:</strong>
                                    </div>
                                    <div class="col text-end">
                                        $<?= number_format($cart->getSubtotal(), 2) ?>
                                    </div>
                                </div>
                                
                                <?php if ($cart->getDiscountAmount() > 0): ?>
                                    <div class="row text-success">
                                        <div class="col">
                                            <strong>Discount (<?= htmlspecialchars($discountInfo) ?>):</strong>
                                        </div>
                                        <div class="col text-end">
                                            -$<?= number_format($cart->getDiscountAmount(), 2) ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="row mt-2 pt-2 border-top">
                                    <div class="col">
                                        <strong>Total:</strong>
                                    </div>
                                    <div class="col text-end">
                                        <strong>$<?= number_format($cart->getTotal(), 2) ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleDiscountConfig() {
            const discountType = document.getElementById('discount_type').value;
            const configs = document.querySelectorAll('.discount-config');
            
            configs.forEach(config => config.classList.remove('active'));
            
            if (discountType === 'percentage') {
                document.getElementById('percentage_config').classList.add('active');
            } else if (discountType === 'buy_x_get_y') {
                document.getElementById('buy_x_get_y_config').classList.add('active');
            }
        }
        
        document.addEventListener('DOMContentLoaded', toggleDiscountConfig);

        window.addEventListener('DOMContentLoaded', function() {
            var notification = document.getElementById('notification-message');
            if (notification) {
                setTimeout(function() {
                    notification.style.display = 'none';
                }, 3000); // 3000ms = 3 seconds
            }
        });
    </script>
</body>
</html>