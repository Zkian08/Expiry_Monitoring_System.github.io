<?php
require 'db.php';
require 'functions.php';
if (is_logged_in()) { header('Location: index.php'); exit; }

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') $msg = 'Missing fields';
    else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE lower(username)=lower(?)");
        $stmt->execute([$username]);
        $u = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($u && password_verify($password, $u['password'])) {
            // store user info in session (without password)
            $_SESSION['user'] = ['id'=>$u['id'],'username'=>$u['username'],'role'=>$u['role'],'department'=>$u['department']];
            header('Location: index.php'); exit;
        } else $msg = 'Invalid credentials';
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Login</title><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="styles.css"></head>
<body class="auth">
  <form class="card" method="post">
    <h2>Sign in</h2>
    <?php if ($msg): ?><div class="alert"><?=$msg?></div><?php endif;?>
    <label>Username<input name="username" required></label>
    <label>Password<input name="password" type="password" required></label>
    <button class="btn">Login</button>
    <p class="muted">No account? <a href="register.php">Register</a></p>
  </form>
</body></html>
