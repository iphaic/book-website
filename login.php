<?php
// login page, checks username and password and logs user in if it matches db

require_once __DIR__ . '/header.php';

 // if ur already logged insend to home page
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = '';

// handle form submit (runs when user hits login button)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // ERROR - make sure both fields were filled out
    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        // look up user row from db by username
        $stmt = $pdo->prepare('SELECT id, username, password FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // if we found a user and the password matches, log them in
            $_SESSION['user_id'] = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            redirect('index.php');
        } else {
            // ERROR - either username doesnt exist or password was wrong
            $error = 'Invalid username or password.';
        }
    }
}
?>

<h2>Login</h2>

<?php if ($error): ?>
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<!-- login form, posts back to this same page -->
<form method="post" action="login.php" class="auth-form">
    <label>
        Username:
        <input type="text" name="username" required>
    </label>
    <label>
        Password:
        <input type="password" name="password" required>
    </label>
    <button type="submit">Login</button>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>