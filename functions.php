<?php
// helper functions

// get the total number of items in a user's cart
// takes:
// $pdo (PDO database connection) (type: PDO)
// $userId (type: int)

// returns:
// int (the number of items in the cart)

function getCartCount(PDO $pdo, int $userId): int
{
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) AS count FROM cart_items WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    return (int)($row['count'] ?? 0);
}

// redirect to given url
// takes:
// $url (string)
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}