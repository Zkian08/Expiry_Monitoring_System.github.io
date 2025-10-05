<?php
// functions.php - helper functions
session_start();

function is_logged_in() {
    return !empty($_SESSION['user']);
}
function current_user() {
    return $_SESSION['user'] ?? null;
}
function require_login() {
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
function require_admin() {
    $u = current_user();
    if (!$u || $u['role'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}
function h($s){ return htmlspecialchars($s ?? '', ENT_QUOTES); }

function csv_response_headers($filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="'.$filename.'"');
}

function parse_csv_text($text) {
    $lines = preg_split("/\\r\\n|\\n|\\r/", $text);
    $rows = [];
    foreach ($lines as $line) {
        if (trim($line) === '') continue;
        $data = str_getcsv($line); // handles quotes
        $rows[] = $data;
    }
    return $rows;
}

