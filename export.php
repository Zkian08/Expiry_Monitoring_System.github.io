<?php
require 'db.php';
require 'functions.php';
require_admin();

// fetch all items
$stmt = $pdo->query("SELECT SKU,Description,department,DateReceive,DateExpiry,QTY,remarks FROM items ORDER BY DateExpiry,department,SKU");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

csv_response_headers('expiry_export_' . date('Y-m-d') . '.csv');
$out = fopen('php://output', 'w');
// header
fputcsv($out, ['SKU','Description','department','Date Receive','Date expiry','QTY','remarks']);
foreach ($rows as $r) {
    fputcsv($out, [$r['SKU'],$r['Description'],$r['department'],$r['DateReceive'],$r['DateExpiry'],$r['QTY'],$r['remarks']]);
}
fclose($out);
exit;
