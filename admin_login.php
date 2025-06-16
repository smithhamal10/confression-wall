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
<!-- HTML FORM (same as before) -->
