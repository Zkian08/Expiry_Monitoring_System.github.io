<?php
require 'db.php';
require 'functions.php';
require_admin();

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) { header('Location: index.php'); exit; }

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
        $stmt = $pdo->prepare("UPDATE items SET SKU=?,Description=?,department=?,DateReceive=?,DateExpiry=?,QTY=?,remarks=? WHERE id=?");
        $stmt->execute([$SKU,$Description,$department,$DateReceive,$DateExpiry,$QTY,$remarks,$id]);
        header('Location: index.php'); exit;
    }
}
?>
<!doctype html><html><head><meta charset="utf-8"><title>Edit Item</title><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="styles.css"></head>
<body class="auth">
  <form class="card" method="post">
    <h2>Edit Item</h2>
    <?php if($msg):?><div class="alert"><?=$msg?></div><?php endif;?>
    <label>SKU<input name="SKU" required value="<?=h($item['SKU'])?>"></label>
    <label>Description<input name="Description" value="<?=h($item['Description'])?>"></label>
    <label>Department<input name="department" value="<?=h($item['department'])?>"></label>
    <label>Date Receive<input name="DateReceive" type="date" value="<?=h($item['DateReceive'])?>"></label>
    <label>Date expiry<input name="DateExpiry" type="date" value="<?=h($item['DateExpiry'])?>"></label>
    <label>QTY<input name="QTY" type="number" value="<?=intval($item['QTY'])?>"></label>
    <label>Remarks<input name="remarks" value="<?=h($item['remarks'])?>"></label>
    <div style="display:flex;gap:.5rem">
      <button class="btn">Save</button>
      <a class="btn muted" href="index.php">Cancel</a>
    </div>
  </form>
</body></html>
