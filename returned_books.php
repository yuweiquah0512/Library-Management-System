<?php
session_start();
date_default_timezone_set('Asia/Singapore'); 

if (!isset($_SESSION['username'])) {
  header("Location: Main.html");
  exit;
}

$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];

// Auto-return overdue books for this user
$now = date('Y-m-d H:i:s');
$conn->query("UPDATE borrow_requests SET returned=1, returned_date='$now' WHERE username='$username' AND returned=0 AND due_date < '$now'");

// Handle delete action
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("UPDATE borrow_requests SET user_deleted=1 WHERE id=$delete_id AND username='$username'");
    header("Location: returned_books.php");
    exit;
}

// Only show records not deleted by user
$sql = "
  SELECT 
    b.title, 
    b.author, 
    b.category, 
    b.language, 
    b.publish_date, 
    b.cover_image, 
    br.request_date, 
    br.due_date, 
    br.returned, 
    br.returned_date,
    br.id AS request_id, 
    br.book_id
  FROM borrow_requests br
  JOIN books b ON br.book_id = b.id
  WHERE br.username = '$username' AND br.returned = 1 AND br.user_deleted = 0
  ORDER BY br.returned_date DESC
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Returned Books</title>
  <link rel="stylesheet" href="design.css">
  <link rel="stylesheet" href="user_sidebar.css">
  <style>
    .delete-btn {
      background-color: #dc3545;
      color: white;
      padding: 8px 18px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 15px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(220, 53, 69, 0.18);
      transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
    }
    .delete-btn:hover {
      background-color: #b52a37;
      box-shadow: 0 8px 25px rgba(220, 53, 69, 0.28);
      transform: translateY(-2px) scale(1.04);
    }
    .returned-books-container {
      max-width: 1500px;
      margin: 40px auto;
      background: rgba(42, 42, 66, 0.98);
      border-radius: 20px;
      box-shadow: 0 12px 40px rgba(0,0,0,0.25), 0 1px 0 rgba(95,158,160,0.08);
      border: 1.5px solid rgba(95, 158, 160, 0.18);
      padding: 36px 32px 32px 32px;
      min-height: 400px;
      margin-bottom: 40px;
    }
    .returned-books-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      margin-bottom: 30px;
    }
    .returned-books-header h1 {
      font-size: 2em;
      font-weight: 700;
      background: linear-gradient(135deg, #5f9ea0 0%, #7fb3b5 50%, #64b5f6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0;
      animation: glow 2s ease-in-out infinite alternate;
    }
    .action-buttons {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
    }
    .action-buttons button {
      width: auto;
      min-width: 180px;
      margin: 0;
    }
    @media (max-width: 900px) {
      .returned-books-container { padding: 18px 6px; }
      .returned-books-header { flex-direction: column; gap: 18px; }
      .action-buttons { flex-direction: column; gap: 10px; }
    }
    .empty-message {
      text-align: center;
      color: #b0b0b0;
      font-size: 1.2em;
      margin: 60px 0 30px 0;
    }
  </style>
</head>
<body>
  <?php include 'user_sidebar.php'; ?>
  <div class="returned-books-container">
    <div class="returned-books-header">
      <h1>Returned Books</h1>
      <div class="action-buttons">
        <a href="borrowed_books.php"><button>View Currently Borrowed Books</button></a>
        <a href="user_dashboard.php"><button>← Back to Dashboard</button></a>
      </div>
    </div>
    <main style="padding:0; background:none; box-shadow:none; border:none;">
      <?php if ($result->num_rows > 0): ?>
        <table>
          <thead>
            <tr>
              <th>Cover</th>
              <th>Title</th>
              <th>Author</th>
              <th>Category</th>
              <th>Language</th>
              <th>Published Date</th>
              <th>Borrowed Date</th>
              <th>Due Date</th>
              <th>Return Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><img src="<?php echo htmlspecialchars($row['cover_image']); ?>" width="50" height="70" alt="Cover"></td>
              <td><?php echo htmlspecialchars($row['title']); ?></td>
              <td><?php echo htmlspecialchars($row['author']); ?></td>
              <td><?php echo htmlspecialchars($row['category']); ?></td>
              <td><?php echo htmlspecialchars($row['language']); ?></td>
              <td><?php echo htmlspecialchars($row['publish_date']); ?></td>
              <td><?php echo htmlspecialchars($row['request_date']); ?></td>
              <td><?php echo htmlspecialchars($row['due_date']); ?></td>
              <td><?php echo htmlspecialchars($row['returned_date']); ?></td>
              <td>
                <a href="returned_books.php?delete_id=<?php echo $row['request_id']; ?>" onclick="return confirm('Are you sure you want to delete this record?');">
                  <button class="delete-btn">Delete</button>
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty-message">You haven't returned any books yet.</div>
      <?php endif; ?>
    </main>
  </div>
</body>
</html>