<?php
require 'db.php';
require 'functions.php';
require_login();

$user = current_user();
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? $user['username']);
    $password = trim($_POST['password'] ?? '');
    $department = trim($_POST['department'] ?? $user['department']);
    
    if ($username === '') {
        $msg = 'Username cannot be empty';
    } else {
        // update DB
        if ($password !== '') {
            $hp = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, department=? WHERE id=?");
            $stmt->execute([$username, $hp, $department, $user['id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, department=? WHERE id=?");
            $stmt->execute([$username, $department, $user['id']]);
        }

        // update session data
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['department'] = $department;
        $msg = 'Profile updated successfully.';
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Profile</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="auth">
  <form class="card" method="post">
    <h2>Edit Profile</h2>
    <?php if ($msg): ?><div class="alert"><?=h($msg)?></div><?php endif;?>
    <label>Username
      <input name="username" value="<?=h($user['username'])?>" required>
    </label>

    <label>New Password (leave blank to keep current)
      <input name="password" type="password">
    </label>

    <?php if ($user['role'] === 'admin'): ?>
      <label>Department (optional)
        <input name="department" value="<?=h($user['department'])?>">
      </label>
    <?php elseif ($user['role'] === 'user'): ?>
      <label>Department (view only)
        <input value="<?=h($user['department'])?>" readonly>
      </label>
    <?php endif; ?>

    <div style="display:flex;gap:.5rem">
      <button class="btn">Save</button>
      <a class="btn muted" href="index.php">Back</a>
    </div>
  </form>
</body>
</html>
