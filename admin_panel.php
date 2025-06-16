<?php
session_start();
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: admin_login.php');
    exit;
}

require 'db.php';
$message = null;

if (isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM reactions WHERE confession_id = ?");
        $stmt->execute([$delete_id]);

        $stmt2 = $pdo->prepare("DELETE FROM confessions WHERE id = ?");
        $stmt2->execute([$delete_id]);

        $pdo->commit();
        $message = "Confession ID $delete_id deleted successfully.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error deleting confession: " . $e->getMessage();
    }
}

$stmt = $pdo->prepare("
    SELECT c.id, c.message, c.mood, c.created_at,
           COALESCE(r.love, 0) AS love,
           COALESCE(r.funny, 0) AS funny,
           COALESCE(r.sad, 0) AS sad,
           COALESCE(r.bold, 0) AS bold
    FROM confessions c
    LEFT JOIN reactions r ON c.id = r.confession_id
    ORDER BY c.created_at DESC
");
$stmt->execute();
$confessions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin Panel - Confessions</title>
  <style>
    body { font-family: Arial; padding: 20px; background: #fff; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
    th { background: #eee; }
    .logout-link { float: right; text-decoration: none; color: red; }
    .message { color: green; font-weight: bold; }
    .delete-btn { background: red; color: white; border: none; padding: 5px 10px; cursor: pointer; }
  </style>
</head>
<body>

<h1>Admin Panel - Confessions</h1>
<p><a href="admin_logout.php" class="logout-link">Logout (<?= htmlspecialchars($_SESSION['admin_username']) ?>)</a></p>

<?php if ($message): ?>
  <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Message</th>
      <th>Mood</th>
      <th>Created At</th>
      <th>Reactions (Love / Funny / Sad / Bold)</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($confessions as $conf): ?>
      <tr>
        <td><?= (int)$conf['id'] ?></td>
        <td><?= htmlspecialchars($conf['message']) ?></td>
        <td><?= htmlspecialchars($conf['mood']) ?></td>
        <td><?= htmlspecialchars($conf['created_at']) ?></td>
        <td><?= (int)$conf['love'] ?> / <?= (int)$conf['funny'] ?> / <?= (int)$conf['sad'] ?> / <?= (int)$conf['bold'] ?></td>
        <td>
          <form method="POST" onsubmit="return confirm('Delete confession ID <?= (int)$conf['id'] ?>?');">
            <input type="hidden" name="delete_id" value="<?= (int)$conf['id'] ?>" />
            <button type="submit" class="delete-btn">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

</body>
</html>
