<?php
// checkout page, shows what's in cart and submit as an order
require_once __DIR__ . '/header.php';

// gotta be logged in to checkout, otherwise back to login
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$userId = (int)$_SESSION['user_id'];

// pull all cart items for this user with book details
$cartStmt = $pdo->prepare('SELECT ci.id AS cart_item_id, ci.quantity, b.id AS book_id, b.title, b.author, b.price FROM cart_items ci JOIN books b ON ci.book_id = b.id WHERE ci.user_id = ? ORDER BY b.title');
$cartStmt->execute([$userId]);
$cartItems = $cartStmt->fetchAll();

// calculate total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

$orderPlaced = false;

// handle the place order button click (form posts back here)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    if (count($cartItems) === 0) {
        // ERROR - tried to checkout with empty cart
        $error = 'Your cart is empty.';
    } else {
        // start transaction
        $pdo->beginTransaction();
        try {
            // create a new row in orders table for this user
            $insertOrder = $pdo->prepare('INSERT INTO orders (user_id, order_date, total) VALUES (?, NOW(), ?)');
            $insertOrder->execute([$userId, $total]);
            $orderId = (int)$pdo->lastInsertId();

            // add each cart item as a line item in order_items
            $insertItem = $pdo->prepare('INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)');
            foreach ($cartItems as $item) {
                $insertItem->execute([$orderId, $item['book_id'], $item['quantity'], $item['price']]);
            }

            // clear all cart rows for this user, cart is now "empty" after order
            $deleteCart = $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?');
            $deleteCart->execute([$userId]);

            // commit the transaction
            $pdo->commit();
            $orderPlaced = true;
        } catch (Exception $e) {
            // something went wrong, undo everything
            $pdo->rollBack();
            $error = 'Something went wrong!';
        }
    }
}
?>

<h2>Checkout</h2>

<?php if (isset($error)): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<?php if ($orderPlaced): ?>
    <!-- user successfully placed order -->
    <p>Thank you! Your order has been placed successfully.</p>
    <p><a href="orders.php">View your order history</a></p>
<?php elseif (count($cartItems) === 0): ?>
    <!-- cart empty -->
    <p>Your cart is empty. <a href="index.php">Continue shopping</a>.</p>
<?php else: ?>
    <!-- show a final summary of cart before user confirms -->
    <table class="cart-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['author']); ?></td>
                    <td>$<?php echo number_format((float)$item['price'], 2); ?></td>
                    <td><?php echo (int)$item['quantity']; ?></td>
                    <td>$<?php echo number_format((float)($item['price'] * $item['quantity']), 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total-label">Total:</td>
                <td class="total-value">$<?php echo number_format((float)$total, 2); ?></td>
            </tr>
        </tfoot>
    </table>
    <form method="post" action="checkout.php">
        <input type="hidden" name="confirm" value="1">
        <button type="submit" class="checkout-button">Place Order</button>
    </form>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>