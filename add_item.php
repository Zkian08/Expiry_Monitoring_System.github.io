<?php
require 'db.php';
require 'functions.php';
require_admin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $SKU = trim($_POST['SKU'] ?? '');
    $Description = trim($_POST['Description'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $DateReceive = trim($_POST['DateReceive'] ?? '');
    $DateExpiry = trim($_POST['DateExpiry'] ?? '');
    $QTY = intval($_POST['QTY'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');
    if ($SKU === '') $msg = 'SKU required';
    else {
        $stmt = $pdo->prepare("INSERT INTO items (SKU,Description,department,DateReceive,DateExpiry,QTY,remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$SKU,$Description,$department,$DateReceive,$DateExpiry,$QTY,$remarks]);
        header('Location: index.php'); exit;
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Add Item</title><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="styles.css"></head>
<body class="auth">
  <form class="card" method="post">
    <h2>Add Item</h2>
    <?php if($msg):?><div class="alert"><?=$msg?></div><?php endif;?>
    <label>SKU<input name="SKU" required></label>
    <label>Description<input name="Description"></label>
    <label>Department<input name="department"></label>
    <label>Date Receive<input name="DateReceive" type="date"></label>
    <label>Date expiry<input name="DateExpiry" type="date"></label>
    <label>QTY<input name="QTY" type="number" value="0"></label>
    <label>Remarks<input name="remarks"></label>
    <div style="display:flex;gap:.5rem">
      <button class="btn">Add</button>
      <a class="btn muted" href="index.php">Cancel</a>
    </div>
  </form>
</body></html>
