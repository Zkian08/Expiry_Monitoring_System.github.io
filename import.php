<?php
require 'db.php';
require 'functions.php';
require_admin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csvfile'])) {
    $replace = !empty($_POST['replace']);
    $file = $_FILES['csvfile']['tmp_name'];
    $text = file_get_contents($file);
    $rows = parse_csv_text($text);
    if (count($rows) <= 0) { $msg = 'No rows found'; }
    else {
        // assume header on first row; map header names
        $header = array_map('trim', $rows[0]);
        $expected = ['SKU','Description','department','Date Receive','Date expiry','QTY','remarks'];
        // normalize header names to known keys
        $map = [];
        foreach ($header as $i => $h) {
            $key = trim($h);
            $map[$i] = $key;
        }
        $dataRows = array_slice($rows, 1);
        if ($replace) {
            $pdo->exec("DELETE FROM items");
        }
        $insert = $pdo->prepare("INSERT INTO items (SKU,Description,department,DateReceive,DateExpiry,QTY,remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $count = 0;
        foreach ($dataRows as $r) {
            // support flexible column naming by index
            $values = [];
            // extract by expected column index if header matched
            // We'll try to map by header names but fallback to order
            $rowAssoc = [];
            foreach ($map as $idx => $colname) {
                $rowAssoc[$colname] = $r[$idx] ?? '';
            }
            // try known names:
            $SKU = $rowAssoc['SKU'] ?? ($r[0] ?? '');
            $Description = $rowAssoc['Description'] ?? ($r[1] ?? '');
            $department = $rowAssoc['department'] ?? ($r[2] ?? '');
            $DateReceive = $rowAssoc['Date Receive'] ?? ($r[3] ?? '');
            $DateExpiry = $rowAssoc['Date expiry'] ?? ($r[4] ?? '');
            $QTY = intval($rowAssoc['QTY'] ?? ($r[5] ?? 0));
            $remarks = $rowAssoc['remarks'] ?? ($r[6] ?? '');
            // if duplicate SKU and not replacing, skip duplicates
            if (!$replace) {
                $check = $pdo->prepare("SELECT COUNT(1) FROM items WHERE SKU = ?");
                $check->execute([$SKU]);
                if ($check->fetchColumn() > 0) continue;
            }
            $insert->execute([$SKU,$Description,$department,$DateReceive,$DateExpiry,$QTY,$remarks]);
            $count++;
        }
        $msg = "Imported $count items.";
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Import CSV</title><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="styles.css"></head>
<body class="auth">
  <form class="card" method="post" enctype="multipart/form-data">
    <h2>Import CSV</h2>
    <?php if ($msg): ?><div class="alert"><?=$msg?></div><?php endif;?>
    <p>CSV columns: <code>SKU,Description,department,Date Receive,Date expiry,QTY,remarks</code></p>
    <input type="file" name="csvfile" accept=".csv,text/csv" required />
    <label><input type="checkbox" name="replace"> Replace existing data (delete before import)</label>
    <div style="display:flex;gap:.5rem">
      <button class="btn">Upload</button>
      <a class="btn muted" href="index.php">Back</a>
    </div>
  </form>
</body></html>
