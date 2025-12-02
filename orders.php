<?php
// order history page, shows all past orders for the logged in user
require_once __DIR__ . '/header.php';

// if ur not logged in, u dont get to see any orders
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$userId = (int)$_SESSION['user_id'];

// grab all orders for this user and sort by newest order_date
$orderStmt = $pdo->prepare('SELECT id, order_date, total FROM orders WHERE user_id = ? ORDER BY order_date DESC');
$orderStmt->execute([$userId]);
$orders = $orderStmt->fetchAll();
?>

<h2>Your Orders</h2>

<?php if (count($orders) === 0): ?>
    <p>You have not placed any orders yet. <a href="index.php">Start shopping</a>.</p>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <div class="order">
            <h3>Order #<?php echo (int)$order['id']; ?> — <?php echo htmlspecialchars($order['order_date']); ?></h3>
            <p>Total: $<?php echo number_format((float)$order['total'], 2); ?></p>
            <?php
            // Fetch items for this order
            $itemsStmt = $pdo->prepare('SELECT b.title, b.author, oi.quantity, oi.price FROM order_items oi JOIN books b ON oi.book_id = b.id WHERE oi.order_id = ?');
            $itemsStmt->execute([(int)$order['id']]);
            $items = $itemsStmt->fetchAll();
            ?>
            <table class="order-items">
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
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><?php echo htmlspecialchars($item['author']); ?></td>
                            <td>$<?php echo number_format((float)$item['price'], 2); ?></td>
                            <td><?php echo (int)$item['quantity']; ?></td>
                            <td>$<?php echo number_format((float)($item['price'] * $item['quantity']), 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>