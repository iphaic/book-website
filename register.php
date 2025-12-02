<?php
// register page, lets new users make an account and has auto log in
// creates a new password hash using a strong one‑way hashing algorithm
require_once __DIR__ . '/header.php';

// if ur already logged in redirect to home page
if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$error = 'error message';

// handle form submit (only runs when user hits the register button)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // grab stuff from form, trim username so we don't get random spaces
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // ERROR - make sure they actually filled everything in
    if ($username === '' || $password === '' || $confirm === '') {
        $error = 'All fields are required.';
    } elseif ($password !== $confirm) {
        // ERROR - user typed 2 different passwords, not allowed
        $error = 'Passwords do not match.';
    } else {
        // check if someone already took this username
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            // ERROR - username exists, ask them to pick a new one
            $error = 'This username is already in use. Please choose another.';
        } else {
             // username is available, so we can create their account now

            // hash the password so we never store the raw password in db
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // insert new user into users sql table
            $insert = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
            $insert->execute([$username, $hashedPassword]);

            // log them in right away using the id we just created
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            $_SESSION['username'] = $username;

            // send them back to homepage after successful registration
            redirect('index.php');
        }
    }
}
?>

<h2>Register</h2>

<?php if ($error): ?>
    <!-- errors -->
    <p class="error"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<!-- registration form, posts back to this same page -->
<form method="post" action="register.php" class="auth-form">
    <label>
        Username:
        <input type="text" name="username" required>
    </label>
    <label>
        Password:
        <input type="password" name="password" required>
    </label>
    <label>
        Confirm Password:
        <input type="password" name="confirm_password" required>
    </label>
    <button type="submit">Register</button>
</form>

<?php require_once __DIR__ . '/footer.php'; ?>