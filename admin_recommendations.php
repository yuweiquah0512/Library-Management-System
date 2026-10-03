<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle "Add" action
if (isset($_GET['add_id'])) {
    $add_id = intval($_GET['add_id']);
    $conn->query("UPDATE book_suggestions SET status='added' WHERE id=$add_id");
    header("Location: admin_recommendations.php?sort={$_GET['sort']}&order={$_GET['order']}&search=" . urlencode($_GET['search']));
    exit();
}

// Handle "Remove" action
if (isset($_GET['remove_id'])) {
    $remove_id = intval($_GET['remove_id']);
    $conn->query("UPDATE book_suggestions SET status='removed' WHERE id=$remove_id");
    header("Location: admin_recommendations.php?sort={$_GET['sort']}&order={$_GET['order']}&search=" . urlencode($_GET['search']));
    exit();
}

// Sorting logic
$sortable = ['id', 'username', 'name', 'title', 'author', 'suggested_at'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $sortable) ? $_GET['sort'] : 'suggested_at';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

// Search logic
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "1";
if ($search !== '') {
    $search_esc = $conn->real_escape_string($search);
    $where .= " AND (username LIKE '%$search_esc%' OR name LIKE '%$search_esc%' OR title LIKE '%$search_esc%' OR author LIKE '%$search_esc%')";
}

$result = $conn->query("SELECT id, username, name, title, author, suggested_at, status FROM book_suggestions WHERE $where ORDER BY $sort $order");
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Book Recommendations</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="admin_sidebar.css">
    <style>
        .added-label { color: #28a745; font-weight: bold; }
        .removed-label { color: #dc3545; font-weight: bold; }
        .notadded-label { color: #ffc107; font-weight: bold; }
        .action-link { margin-right: 10px; }
        .search-bar-container {
            max-width: 1200px;
            margin: 0 auto 24px auto;
            width: 100%;
        }
        .search-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            gap: 10px;
        }
        .search-bar input[type="text"] {
            flex: 1 1 0%;
            min-width: 0;
            font-size: 1em;
            padding: 14px 16px;
        }
        .search-bar button,
        .search-bar a > button {
            min-width: 140px;
            font-size: 1em;
            padding: 14px 0;
        }
        .recommendations-list-container {
            max-width: 1200px;
            margin: 32px auto 0 auto;
            background: rgba(42, 42, 66, 0.98);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 1px 0 rgba(95,158,160,0.08);
            border: 1.5px solid rgba(95, 158, 160, 0.18);
            padding: 28px 18px 18px 18px;
        }
        .recommendations-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: none;
            border-radius: 14px;
            overflow: hidden;
            margin: 0;
        }
        .recommendations-table th, .recommendations-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #5f9ea0;
            text-align: left;
            color: #e0e0e0;
            font-size: 1em;
        }
        .recommendations-table th {
            background: #1a1a1a;
            color: #5f9ea0;
            font-weight: 700;
            border-top: none;
        }
        .recommendations-table tr:last-child td {
            border-bottom: none;
        }
        .recommendations-table tr {
            transition: background 0.2s;
        }
        .recommendations-table tbody tr:hover td {
            background: #232b3b;
        }
        .recommendations-table td {
            vertical-align: middle;
        }
        .recommendations-table .action-link span,
        .recommendations-table .added-label,
        .recommendations-table .removed-label,
        .recommendations-table .notadded-label {
            font-size: 1em;
            border-radius: 6px;
            padding: 4px 10px;
            display: inline-block;
        }
        @media (max-width: 900px) {
            .recommendations-list-container { padding: 8px 2vw; }
            .recommendations-table th, .recommendations-table td { padding: 10px 4px; font-size: 0.95em; }
        }
    </style>
    <script>
        function sortTable(column) {
            const url = new URL(window.location.href);
            let order = url.searchParams.get('order') === 'ASC' ? 'DESC' : 'ASC';
            if (url.searchParams.get('sort') !== column) order = 'ASC';
            url.searchParams.set('sort', column);
            url.searchParams.set('order', order);
            window.location.href = url.toString();
        }
    </script>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="header" style="margin: 12px 30px 18px 30px; padding: 16px 24px; border-radius: 14px;">
      <div class="header-content" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <h2 style="font-size:2.2em; font-weight:700; background:linear-gradient(135deg,#5f9ea0 0%,#7fb3b5 50%,#64b5f6 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0;animation:glow 2s ease-in-out infinite alternate;">User Book Recommendations</h2>
        <div class="btn-container" style="display:flex; gap:12px; align-items:center; margin-top:0;">
          <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">← Back To Dashboard</a>
          <a href="add_book.php" class="btn btn-primary btn-sm">Add New Books</a>
        </div>
      </div>
    </div>

    <div class="search-bar-container">
      <form method="get" class="search-bar">
        <input type="text" name="search" placeholder="Search Username / Name / Title / Author" value="<?php echo htmlspecialchars($search); ?>">
        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
        <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
        <a href="admin_recommendations.php" style="text-decoration:none;"><button type="button" class="btn btn-secondary btn-sm">Reset</button></a>
      </form>
    </div>

    <div class="recommendations-list-container">
      <?php if ($result && $result->num_rows > 0): ?>
      <table class="recommendations-table">
          <thead>
            <tr>
                <th onclick="sortTable('name')">Name</th>
                <th onclick="sortTable('username')">Username</th>
                <th onclick="sortTable('title')">Title</th>
                <th onclick="sortTable('author')">Author</th>
                <th onclick="sortTable('suggested_at')">Suggested At</th>
                <th>Action</th>
            </tr>
          </thead>
          <tbody>
          <?php while($row = $result->fetch_assoc()): ?>
          <tr>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['username']); ?></td>
              <td><?php echo htmlspecialchars($row['title']); ?></td>
              <td><?php echo htmlspecialchars($row['author']); ?></td>
              <td><?php echo htmlspecialchars($row['suggested_at']); ?></td>
              <td>
                  <?php if ($row['status'] === 'added'): ?>
                      <span class="added-label">Added</span>
                  <?php elseif ($row['status'] === 'removed'): ?>
                      <span class="removed-label">Removed</span>
                  <?php else: ?>
                      <a class="action-link" href="?add_id=<?php echo $row['id']; ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&search=<?php echo urlencode($search); ?>"
                         onclick="return confirm('Mark this suggestion as added?');">
                          <span class="notadded-label">Not Added</span>
                      </a>
                      <a class="action-link" href="?remove_id=<?php echo $row['id']; ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&search=<?php echo urlencode($search); ?>"
                         onclick="return confirm('Are you sure you want to remove this suggestion?');">
                          <span class="removed-label">Remove</span>
                      </a>
                  <?php endif; ?>
              </td>
          </tr>
          <?php endwhile; ?>
          </tbody>
      </table>
      <?php else: ?>
          <p style="text-align:center;">No recommendations found.</p>
      <?php endif; ?>
    </div>
</body>
</html>