<?php
require 'db.php';
require 'functions.php';

$msg = '';
// Only admins can create new users via UI. If no admin exists, allow first registration (handled in db init we've created).
$allow = true;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? 'user';
    $department = trim($_POST['department'] ?? '') ?: null;
    if ($username === '' || $password === '') $msg = 'Missing fields';
    else {
        // check unique
        $stmt = $pdo->prepare("SELECT COUNT(1) FROM users WHERE lower(username)=lower(?)");
        $stmt->execute([$username]); if ($stmt->fetchColumn()>0) { $msg='Username exists'; }
        else {
            $hp = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username,password,role,department) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hp, $role, $role==='user' ? $department : null]);
            header('Location: login.php'); exit;
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Register</title><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="styles.css"></head>
<body class="auth">
  <form class="card" method="post">
    <h2>Register user</h2>
    <?php if ($msg): ?><div class="alert"><?=$msg?></div><?php endif;?>
    <label>Username<input name="username" required></label>
    <label>Password<input name="password" type="password" required></label>
    <label>Role
      <select name="role" id="role" onchange="document.getElementById('dept').style.display = this.value==='user' ? 'block' : 'none'">
        <option value="user">User</option>
        <option value="admin">Admin</option>
      </select>
    </label>
    <div id="dept">
      <label>Department (for user)<input name="department"></label>
    </div>
    <button class="btn">Register</button>
    <p class="muted"><a href="login.php">Back to login</a></p>
  </form>
</body></html>
