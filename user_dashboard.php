<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Sorting logic
$sortable = ['title', 'author', 'category', 'copies'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $sortable) ? $_GET['sort'] : 'title';

$result = $conn->query("SELECT * FROM books ORDER BY $sort");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Dashboard</title>
  <link rel="stylesheet" href="design.css">
  <link rel="stylesheet" href="user_sidebar.css">
  <link rel="stylesheet" href="admin.css">
  <style>
    .dashboard-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
      min-height: 100vh;
    }
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
      margin-bottom: 30px;
    }
    .dashboard-card {
      background: rgba(42, 42, 66, 0.95);
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);
      backdrop-filter: blur(15px);
      border: 1px solid rgba(95, 158, 160, 0.3);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      min-height: 140px;
    }
    .dashboard-card:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 30px 60px rgba(0,0,0,0.4), 0 0 50px rgba(95, 158, 160, 0.2);
      border-color: rgba(95, 158, 160, 0.6);
    }
    .dashboard-card .card-title {
      font-size: 1.2em;
      font-weight: 700;
      background: linear-gradient(135deg, #5f9ea0, #7fb3b5);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 6px;
    }
    .dashboard-card .card-desc {
      color: #b0b0b0;
      font-size: 0.98em;
      margin-bottom: 10px;
    }
    .dashboard-card .card-link {
      color: #5f9ea0;
      text-decoration: none;
      font-weight: 600;
      border-bottom: 2px solid transparent;
      transition: all 0.3s;
    }
    .dashboard-card .card-link:hover {
      color: #7fb3b5;
      border-bottom-color: #5f9ea0;
    }
    .dashboard-header {
      background: rgba(42, 42, 66, 0.95);
      border-radius: 20px;
      padding: 30px 40px;
      margin-bottom: 30px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);
      border: 1px solid rgba(95, 158, 160, 0.3);
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 20px;
    }
    .dashboard-header h1 {
      font-size: 2.2em;
      font-weight: 700;
      background: linear-gradient(135deg, #5f9ea0 0%, #7fb3b5 50%, #64b5f6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0;
      animation: glow 2s ease-in-out infinite alternate;
    }
    @media (max-width: 768px) {
      .dashboard-header { flex-direction: column; text-align: center; }
      .dashboard-header h1 { font-size: 1.5em; }
      .dashboard-grid { grid-template-columns: 1fr; }
    }
    .books-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 22px;
      margin-top: 24px;
    }
    .book-card {
      background: rgba(42, 42, 66, 0.95);
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.18), 0 1px 0 rgba(255,255,255,0.08);
      border: 1px solid rgba(95, 158, 160, 0.18);
      padding: 18px 18px 14px 18px;
      display: flex;
      align-items: flex-start;
      gap: 16px;
      cursor: pointer;
      transition: box-shadow 0.3s, border-color 0.3s, transform 0.2s;
      position: relative;
      min-height: 120px;
    }
    .book-card:hover {
      box-shadow: 0 16px 40px rgba(95,158,160,0.18), 0 0 30px rgba(95,158,160,0.12);
      border-color: #5f9ea0;
      transform: translateY(-4px) scale(1.03);
    }
    .book-card img {
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.18);
      background: #232b2b;
      object-fit: cover;
      flex-shrink: 0;
    }
    .book-info {
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 4px;
    }
    .book-title {
      font-size: 1.08em;
      font-weight: 700;
      color: #5f9ea0;
      margin-bottom: 2px;
      line-height: 1.2;
    }
    .book-author {
      color: #b0b0b0;
      font-size: 0.98em;
      margin-bottom: 2px;
    }
    .book-meta {
      font-size: 0.93em;
      color: #e0e0e0;
      margin-top: 2px;
    }
    .book-category {
      color: #7fb3b5;
      font-weight: 600;
    }
    .book-copies {
      color: #b0b0b0;
    }
    @media (max-width: 600px) {
      .books-grid { grid-template-columns: 1fr; }
      .book-card { flex-direction: column; align-items: center; text-align: center; }
      .book-card img { margin-bottom: 10px; }
      .book-info { align-items: center; }
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
  <?php include 'user_sidebar.php'; ?>
  <div class="dashboard-container">
    <div class="dashboard-header">
      <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
      <a href="Main.html" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </div>
    <div class="dashboard-grid">
      <div class="dashboard-card" onclick="window.location.href='borrowed_books.php'">
        <span class="card-title">View Borrowed Books</span>
        <span class="card-desc">See all books you are currently borrowing.</span>
        <a href="borrowed_books.php" class="card-link">Go to Borrowed Books →</a>
      </div>
      <div class="dashboard-card" onclick="window.location.href='returned_books.php'">
        <span class="card-title">View Returned Books</span>
        <span class="card-desc">Check your history of returned books.</span>
        <a href="returned_books.php" class="card-link">Go to Returned Books →</a>
      </div>
      <div class="dashboard-card" onclick="window.location.href='category.php'">
        <span class="card-title">View by Category</span>
        <span class="card-desc">Browse books by your favorite categories.</span>
        <a href="category.php" class="card-link">Browse Categories →</a>
      </div>
      <div class="dashboard-card" onclick="window.location.href='user_settings.php'">
        <span class="card-title">Settings</span>
        <span class="card-desc">Update your profile and account settings.</span>
        <a href="user_settings.php" class="card-link">Go to Settings →</a>
      </div>
      <div class="dashboard-card" onclick="window.location.href='user_recommendations.php'">
        <span class="card-title">Recommendations</span>
        <span class="card-desc">Suggest new books and view your suggestions.</span>
        <a href="user_recommendations.php" class="card-link">Go to Recommendations →</a>
      </div>
    </div>
    <section>
      <h2>Books Available</h2>
      <input type="text" id="searchBox" placeholder="Search books...">
      <div class="books-grid">
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="book-card" onclick="window.location.href='book_detail.php?id=<?php echo $row['id']; ?>'">
          <img src="<?php echo htmlspecialchars($row['cover_image']); ?>" width="70" height="100" alt="Cover">
          <div class="book-info">
            <div class="book-title"><?php echo htmlspecialchars($row['title']); ?></div>
            <div class="book-author">by <?php echo htmlspecialchars($row['author']); ?></div>
            <div class="book-meta">
              <span class="book-category"><?php echo htmlspecialchars($row['category']); ?></span> |
              <span class="book-copies"><?php echo (int)$row['copies']; ?> copies</span>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </section>
  </div>
  <script src="script.js"></script>
</body>
</html>