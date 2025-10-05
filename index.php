<?php
require 'db.php';
require 'functions.php';

// filters & search
$filterDept = $_GET['department'] ?? '';
$search = $_GET['q'] ?? '';

$user = current_user();
require_login();

// if user role is 'user', enforce department filter
if ($user['role'] === 'user') {
    $filterDept = $user['department'];
}

// build query
$sql = "SELECT * FROM items WHERE 1=1";
$params = [];
if ($filterDept !== '') {
    $sql .= " AND lower(department) = lower(?)";
    $params[] = $filterDept;
}
if ($search !== '') {
    $sql .= " AND (lower(SKU) LIKE ? OR lower(Description) LIKE ? OR lower(remarks) LIKE ?)";
    $q = '%' . strtolower($search) . '%';
    $params[] = $q; $params[] = $q; $params[] = $q;
}
$sql .= " ORDER BY DateExpiry ASC, department, SKU";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// fetch departments for filter list
$deptStmt = $pdo->query("SELECT DISTINCT department FROM items ORDER BY department");
$departments = $deptStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Expiry Monitor</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="styles.css">
</head>
<body class="app">
  <nav class="topbar">
    <div class="brand">Expiry Monitor</div>
    <div class="nav-right">
      <form method="get" class="inline">
        <input name="q" placeholder="search SKU,desc,remarks" value="<?=h($search)?>" />
        <?php if($user['role']!=='user'): ?>
          <select name="department">
            <option value="">All departments</option>
            <?php foreach($departments as $d): ?>
              <option value="<?=h($d)?>" <?= $filterDept===$d ? 'selected':''?>><?=h($d)?></option>
            <?php endforeach;?>
          </select>
        <?php else: ?>
          <input type="hidden" name="department" value="<?=h($filterDept)?>" />
        <?php endif;?>
        <button>Filter</button>
      </form>

      <div class="userbox">
  <a class="btn" href="profile.php"><?=h($user['username'])?></a>
  <span>(<?=h($user['role'])?><?= $user['role']==='user' ? '/ '.h($user['department']) : '' ?>)</span>
  <a class="btn" href="logout.php">Logout</a>
</div>

    </div>
  </nav>

  <main class="container">
    <header class="controls">
      <?php if ($user['role'] === 'admin'): ?>
        <a class="btn" href="import.php">Import CSV</a>
        <a class="btn" href="add_item.php">Add Item</a>
        <a class="btn" href="export.php">Export CSV</a>
        <a class="btn" href="register.php">Add User</a>
      <?php endif; ?>
    </header>

    <section class="panel">
      <h2>Items (<?=count($items)?>)</h2>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>SKU</th><th>Description</th><th>Department</th><th>Receive</th><th>Expiry</th><th>QTY</th><th>Remarks</th>
              <?php if ($user['role']==='admin') echo '<th>Actions</th>'; ?>
            </tr>
          </thead>
          <tbody>
            <?php
            $today = new DateTimeImmutable();
            foreach ($items as $it):
              $exp = $it['DateExpiry'] ? DateTimeImmutable::createFromFormat('Y-m-d', $it['DateExpiry']) : null;
              $days = $exp ? intval(($exp->getTimestamp() - $today->getTimestamp())/86400) : null;
              $rowClass = '';
              if ($days !== null) {
                if ($days < 0) $rowClass = 'expired';
                elseif ($days <= 30) $rowClass = 'near-expiry';
              }
            ?>
            <tr class="<?=h($rowClass)?>">
              <td><?=h($it['SKU'])?></td>
              <td><?=h($it['Description'])?></td>
              <td><?=h($it['department'])?></td>
              <td><?=h($it['DateReceive'])?></td>
              <td><?=h($it['DateExpiry'])?> <?= $days !== null ? '(' . $days . 'd)' : '' ?></td>
              <td><?=h($it['QTY'])?></td>
              <td><?=h($it['remarks'])?></td>
              <?php if ($user['role']==='admin'): ?>
                <td>
                  <a class="small" href="edit_item.php?id=<?=intval($it['id'])?>">Edit</a>
                  <a class="small danger" href="delete_item.php?id=<?=intval($it['id'])?>" onclick="return confirm('Delete item?')">Delete</a>
                </td>
              <?php endif;?>
            </tr>
            <?php endforeach;?>
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <footer class="footer">
    <small>© Enriquez, Neil Ian G. Monitoring System 30 Days Report</small>
  </footer>
</body>
</html>
