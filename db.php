<?php
// db.php - SQLite connection and initialization
$dbFile = __DIR__ . '/data/database.sqlite';
if (!is_dir(__DIR__ . '/data')) mkdir(__DIR__ . '/data', 0755, true);

$init = !file_exists($dbFile);
$pdo = new PDO('sqlite:' . $dbFile);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if ($init) {
    // users table: username unique, password hashed, role ('admin'|'user'), department nullable
    $pdo->exec("
    CREATE TABLE users (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      username TEXT UNIQUE NOT NULL,
      password TEXT NOT NULL,
      role TEXT NOT NULL,
      department TEXT
    );
    ");

    // items table
    $pdo->exec("
    CREATE TABLE items (
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      SKU TEXT NOT NULL,
      Description TEXT,
      department TEXT,
      DateReceive TEXT,
      DateExpiry TEXT,
      QTY INTEGER,
      remarks TEXT
    );
    ");

    // insert sample users (admin + IAN user)
    $stmt = $pdo->prepare("INSERT INTO users (username,password,role,department) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', password_hash('admin123', PASSWORD_DEFAULT), 'admin', null]);
    $stmt->execute(['IAN', password_hash('password', PASSWORD_DEFAULT), 'user', 'Chocolate']);

    // sample items
    $items = [
      ['1001','Dark Chocolate Bar','Chocolate','2025-01-15','2025-11-01',50,'OK'],
      ['1002','Milk Chocolate Box','Chocolate','2025-03-10','2025-10-15',20,'Fragile'],
      ['2001','Espresso Beans','Coffee','2025-02-01','2026-02-01',100,'Store cool']
    ];
    $stmt = $pdo->prepare("INSERT INTO items (SKU,Description,department,DateReceive,DateExpiry,QTY,remarks) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($items as $it) $stmt->execute($it);
}
