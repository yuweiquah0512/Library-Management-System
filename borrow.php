<?php
session_start();
date_default_timezone_set('Asia/Singapore');
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle book return if request_id is provided
if (isset($_GET['return_id'])) {
    $request_id = intval($_GET['return_id']);
    $now = date('Y-m-d H:i:s');
    $conn->query("UPDATE borrow_requests SET returned=1, returned_date='$now' WHERE id=$request_id");
    header("Location: borrow.php");
    exit();
}

// --- Search and Sort Logic ---
$sortable = ['username', 'user_fullname', 'title', 'author', 'request_date', 'due_date'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $sortable) ? $_GET['sort'] : 'due_date';
$order = isset($_GET['order']) ? (strtolower($_GET['order']) === 'asc' ? 'ASC' : 'DESC') : 'ASC';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$where = "br.returned = 0";
if ($search !== '') {
    $search_esc = $conn->real_escape_string($search);
    $where .= " AND (br.username LIKE '%$search_esc%' OR u.name LIKE '%$search_esc%' OR b.title LIKE '%$search_esc%' OR b.author LIKE '%$search_esc%')";
}

$sql = "
  SELECT 
    br.id AS request_id,
    br.username,
    u.name AS user_fullname,
    b.title,
    b.author, 
    b.cover_image, 
    br.request_date, 
    br.due_date, 
    br.returned,
    br.returned_date
  FROM borrow_requests br
  JOIN books b ON br.book_id = b.id
  JOIN users u ON br.username = u.username
  WHERE $where
  ORDER BY $sort $order
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borrowed Books</title>
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="admin_sidebar.css">
  <style>
    .borrowed-list-container {
      max-width: 1500px;
      margin: 32px auto 0 auto;
      background: rgba(42, 42, 66, 0.98);
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 1px 0 rgba(95,158,160,0.08);
      border: 1.5px solid rgba(95, 158, 160, 0.18);
      padding: 28px 18px 18px 18px;
    }
    .borrowed-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
      background: none;
      overflow: hidden;
      margin: 0;
    }
    .borrowed-table th, .borrowed-table td {
      padding: 16px 12px;
      border-bottom: 1px solid #5f9ea0;
      text-align: left;
      color: #e0e0e0;
      font-size: 1em;
    }
    .borrowed-table th {
      background: #1a1a1a;
      color: #5f9ea0;
      font-weight: 700;
      border-top: none;
      cursor: pointer;
    }
    .borrowed-table tr:last-child td {
      border-bottom: none;
    }
    .borrowed-table tr {
      transition: background 0.2s;
    }
    .borrowed-table tbody tr:hover td {
      background: #232b3b;
    }
    .borrowed-table td {
      vertical-align: middle;
    }
    .return-btn {
      background: none;
      color: #1976d2;
      border: none;
      border-radius: 0;
      font-family: 'Times New Roman', Times, serif;
      font-size: 1em;
      font-weight: 600;
      padding: 0;
      margin: 0;
      box-shadow: none;
      cursor: pointer;
      text-decoration: underline;
      transition: color 0.2s;
      display: inline;
    }
    .return-btn:hover {
      color: #0d47a1;
      text-decoration: underline;
    }
    @media (max-width: 900px) {
      .borrowed-list-container { padding: 8px 2vw; }
      .borrowed-table th, .borrowed-table td { padding: 10px 4px; font-size: 0.95em; }
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
      <h2 style="font-size:2.2em; font-weight:700; background:linear-gradient(135deg,#5f9ea0 0%,#7fb3b5 50%,#64b5f6 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0;animation:glow 2s ease-in-out infinite alternate;">Borrowed Books</h2>
      <div class="btn-container" style="display:flex; gap:12px; align-items:center; margin-top:0;">
        <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">← Back To Dashboard</a>
        <a href="return.php" class="btn btn-primary btn-sm">View Returned Books</a>
      </div>
    </div>
  </div>
  <div class="search-bar-container" style="max-width:1200px;margin:0 auto 24px auto;width:100%;">
    <form method="get" class="search-bar" style="display:flex;align-items:center;justify-content:center;width:100%;gap:10px;">
      <input type="text" name="search" placeholder="Search Name / Username / Title / Author" value="<?php echo htmlspecialchars($search); ?>">
      <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort); ?>">
      <input type="hidden" name="order" value="<?php echo htmlspecialchars($order); ?>">
      <button type="submit" class="btn btn-primary btn-sm">Search</button>
      <a href="borrow.php" style="text-decoration:none;"><button type="button" class="btn btn-secondary btn-sm">Reset</button></a>
    </form>
  </div>
  <div class="borrowed-list-container">
    <?php if ($result && $result->num_rows > 0): ?>
      <table class="borrowed-table">
        <thead>
          <tr>
            <th onclick="sortTable('username')">User</th>
            <th onclick="sortTable('user_fullname')">UserName</th>
            <th>Cover</th>
            <th onclick="sortTable('title')">Title</th>
            <th onclick="sortTable('author')">Author</th>
            <th onclick="sortTable('request_date')">Borrow Date</th>
            <th onclick="sortTable('due_date')">Due Date</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['user_fullname']); ?></td>
            <td><img src="<?php echo htmlspecialchars($row['cover_image']); ?>" width="50" height="70" alt="Cover"></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['author']); ?></td>
            <td><?php echo htmlspecialchars($row['request_date']); ?></td>
            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
            <td>
              <a href="borrow.php?return_id=<?php echo $row['request_id']; ?>" onclick="return confirm('Are you sure you want to mark this book as returned?');">
                <button class="return-btn">Mark as Returned</button>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p style="text-align:center;">No currently borrowed books found.</p>
    <?php endif; ?>
  </div>