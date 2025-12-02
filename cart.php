<?php
// cart page for logged in user
require_once __DIR__ . '/header.php';

// if ur not logged in, kick to login page
if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$userId = (int)$_SESSION['user_id'];

// handle any cart actions sent via post (add, update, remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bookId = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;
    $quantity = isset($_POST['quantity']) ? max(1, (int)$_POST['quantity']) : 1;
    $cartItemId = isset($_POST['cart_item_id']) ? (int)$_POST['cart_item_id'] : 0;

    switch ($action) {
        case 'add':
            // add a book to cart. if it already exists, just +1 the quantity instead of making a new row
            $check = $pdo->prepare('SELECT id, quantity FROM cart_items WHERE user_id = ? AND book_id = ?');
            $check->execute([$userId, $bookId]);
            $existing = $check->fetch();
            if ($existing) {
                $newQuantity = $existing['quantity'] + $quantity;
                $update = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ?');
                $update->execute([$newQuantity, $existing['id']]);
            } else {
                $insert = $pdo->prepare('INSERT INTO cart_items (user_id, book_id, quantity) VALUES (?, ?, ?)');
                $insert->execute([$userId, $bookId, $quantity]);
            }
            break;
        case 'update':
            // user manually changed the quantity input, so sync db with whatever they typed
            if ($cartItemId > 0) {
                $update = $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?');
                $update->execute([$quantity, $cartItemId, $userId]);
            }
            break;
        case 'remove':
            // remove a cart item
            if ($cartItemId > 0) {
                $delete = $pdo->prepare('DELETE FROM cart_items WHERE id = ? AND user_id = ?');
                $delete->execute([$cartItemId, $userId]);
            }
            break;
    }
    // after doing any action, reload the page so refreshing doesn’t re-submit the form. redirect(url) is a small function that can be found in functions.php
    redirect('cart.php');
}

// get all cart items for this user anbd their book details (title, price, etc)
$cartStmt = $pdo->prepare('SELECT ci.id AS cart_item_id, ci.quantity, b.id AS book_id, b.title, b.author, b.price FROM cart_items ci JOIN books b ON ci.book_id = b.id WHERE ci.user_id = ? ORDER BY b.title');
$cartStmt->execute([$userId]);
$cartItems = $cartStmt->fetchAll();

// calculate cart total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<h2>Your Cart</h2>

<?php if (count($cartItems) === 0): ?>
    <p>Your cart is empty. <a href="index.php">Continue shopping</a>.</p>
<?php else: ?>
    <table class="cart-table">
        <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo htmlspecialchars($item['author']); ?></td>
                    <td>$<?php echo number_format((float)$item['price'], 2); ?></td>
                    <td>
                        <form method="post" action="cart.php" class="update-form">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="cart_item_id" value="<?php echo (int)$item['cart_item_id']; ?>">
                            <input type="number" name="quantity" value="<?php echo (int)$item['quantity']; ?>" min="1" required>
                            <button type="submit">Update</button>
                        </form>
                    </td>
                    <td>$<?php echo number_format((float)($item['price'] * $item['quantity']), 2); ?></td>
                    <td>
                        <form method="post" action="cart.php" class="remove-form" onsubmit="return confirm('Remove this item from your cart?');">
                            <input type="hidden" name="action" value="remove">
                            <input type="hidden" name="cart_item_id" value="<?php echo (int)$item['cart_item_id']; ?>">
                            <button type="submit">Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total-label">Total:</td>
                <td colspan="2" class="total-value">$<?php echo number_format((float)$total, 2); ?></td>
            </tr>
        </tfoot>
    </table>
    <p><a href="checkout.php" class="checkout-button">Proceed to Checkout</a></p>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>