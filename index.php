<?php
// main homepage, shows all books and lets u filter by category
require_once __DIR__ . '/header.php';

// grab all unique categories from books table to dynamically make the filter buttons
$categoryStmt = $pdo->query("SELECT DISTINCT category FROM books ORDER BY category ASC");
$categories = $categoryStmt->fetchAll();

// see if user clicked a specific category in the url (?category=...)
$selectedCategory = $_GET['category'] ?? '';

// pull books from db, either all of them or just the chosen category
if ($selectedCategory) {
    $bookStmt = $pdo->prepare("SELECT * FROM books WHERE category = ? ORDER BY title ASC");
    $bookStmt->execute([$selectedCategory]);
} else {
    $bookStmt = $pdo->query("SELECT * FROM books ORDER BY title ASC");
}
$books = $bookStmt->fetchAll();
?>

<h2>Book Catalog</h2>

<!-- category filter bar, lets u switch between all / specific categories -->
<div class="categories">
    <strong>Categories:</strong>
    <a href="index.php"<?php if ($selectedCategory === '') echo ' class="selected"'; ?>>All</a>
    <?php foreach ($categories as $cat): ?>
        <?php $catName = htmlspecialchars($cat['category']); ?>
        <a href="index.php?category=<?php echo urlencode($catName); ?>"<?php if ($selectedCategory === $cat['category']) echo ' class="selected"'; ?>><?php echo $catName; ?></a>
    <?php endforeach; ?>
</div>

<!-- list of all the books from db -->
<div class="book-list">
    <?php if (count($books) === 0): ?>
        <p>No books found in this category.</p>
    <?php else: ?>
        <?php foreach ($books as $book): ?>
            <div class="book-item">
                <?php
                // default to placeholder cover
                $imageFile = 'no_image.png';

                if (!empty($book['image'])) {
                    // if db already has a filename, try to use that (only if file actually exists)
                    $candidate = $book['image'];
                    if (file_exists(__DIR__ . '/images/' . $candidate)) {
                        $imageFile = $candidate;
                    }
                } else {
                    $check = strtolower($book['title']);

                    // remove apostrophes entirely so "sorcerer's" -> "sorcerers"
                    $check = str_replace("'", '', $check);
                    
                    // replace non a–z / 0–9 with dashes
                    $check = preg_replace('/[^a-z0-9]+/', '-', $check);
                    
                    // trim extra dashes from start/end
                    $check = trim($check, '-');

                    // try these image types
                    $possibleFiles = [
                        $check . '.png',
                        $check . '.jpg',
                        $check . '.jpeg',
                    ];

                    foreach ($possibleFiles as $file) {
                        if (file_exists(__DIR__ . '/images/' . $file)) {
                            $imageFile = $file;
                            break;
                        }
                    }
                }
                ?>
                <img src="images/<?php echo htmlspecialchars($imageFile); ?>"
                     alt="<?php echo htmlspecialchars($book['title']); ?>">

                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                <p class="author">by <?php echo htmlspecialchars($book['author']); ?></p>
                <p class="category">Category: <?php echo htmlspecialchars($book['category']); ?></p>
                <p class="price">$<?php echo number_format((float)$book['price'], 2); ?></p>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="post" action="cart.php" class="add-cart-form">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="book_id" value="<?php echo (int)$book['id']; ?>">
                        <label>
                            Quantity:
                            <input type="number" name="quantity" value="1" min="1" required>
                        </label>
                        <button type="submit">Add to Cart</button>
                    </form>
                <?php else: ?>
                    <p class="login-message"><a href="login.php">Login to add to cart</a></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>


<?php require_once __DIR__ . '/footer.php'; ?>