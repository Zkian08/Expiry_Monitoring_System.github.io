<?php
require 'db.php';
require 'functions.php';
require_admin();
$id = intval($_GET['id'] ?? 0);
$pdo->prepare("DELETE FROM items WHERE id=?")->execute([$id]);
header('Location: index.php');
exit;
