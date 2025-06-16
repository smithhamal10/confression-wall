<?php
session_start();
require 'db.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_username'] = $admin['username'];
            header('Location: admin_panel.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    } else {
        $errors[] = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Login</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 50px; }
    form { max-width: 300px; margin: auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1);}
    input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; }
    button { width: 100%; padding: 10px; background: #333; color: white; border: none; cursor: pointer; }
    button:hover { background: #555; }
    .error { color: red; margin-bottom: 10px; }
  </style>
</head>
<body>
  <h2 style="text-align:center;">Admin Login</h2>

  <?php if ($errors): ?>
    <div class="error"><?= htmlspecialchars($errors[0]) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <label>Username:</label>
    <input type="text" name="username" required autofocus />

    <label>Password:</label>
    <input type="password" name="password" required />

    <button type="submit">Login</button>
  </form>
</body>
</html>
