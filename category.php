<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: index.html");
  exit;
}

$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$selectedCategory = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : null;
$categoriesResult = $conn->query("SELECT DISTINCT category FROM books WHERE category IS NOT NULL AND category != ''");

if ($selectedCategory) {
  $booksResult = $conn->query("SELECT * FROM books WHERE category = '$selectedCategory'");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Browse by Category</title>
  <link rel="stylesheet" href="design.css">
  <link rel="stylesheet" href="user_sidebar.css">
  <link rel="stylesheet" href="admin.css">
  <style>
    .category-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 30px 20px 20px 20px;
      min-height: 100vh;
    }
    .category-header {
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
    .category-header h1 {
      font-size: 2.1em;
      font-weight: 700;
      background: linear-gradient(135deg, #5f9ea0 0%, #7fb3b5 50%, #64b5f6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0;
      animation: glow 2s ease-in-out infinite alternate;
    }
    .category-list {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
      justify-items: center;
      margin-bottom: 40px;
      width: 100%;
      padding: 0 0 0 0;
    }
    .category-button {
      min-width: 300px;
      font-size: 1.1em;
      width: 100%;
      box-sizing: border-box;
      background: linear-gradient(135deg, #232b2b 60%, #2a2a2a 100%);
      color: #5f9ea0;
      font-weight: 700;
      border: 1.5px solid #5f9ea0;
      border-radius: 18px;
      box-shadow: 0 4px 18px rgba(95, 158, 160, 0.10), 0 1.5px 0 rgba(95,158,160,0.08);
      transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
      outline: none;
      letter-spacing: 1px;
      font-family: 'Times New Roman', Times, serif;
      padding: 18px 0;
      margin: 0;
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    .category-button:hover, .category-button:focus {
      background: linear-gradient(135deg,rgb(87, 87, 87) 60%, #5f9ea0 100%);
      color: #232b2b;
      border-color: #7fb3b5;
      box-shadow: 0 8px 32px rgba(95, 158, 160, 0.22), 0 0 0 2px #5f9ea0;
      transform: scale(1.04);
      z-index: 1;
    }
    .books-section {
      margin-top: 30px;
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
      width: 70px;
      height: 100px;
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
    .book-copies {
      color: #b0b0b0;
    }
    .book-category {
      color: #7fb3b5;
      font-weight: 600;
    }
    .category-nav-btns {
      display: flex;
      gap: 16px;
      margin-top: 30px;
      justify-content: center;
    }
    main {
      max-width: 100%;
      margin: 40px auto 0 auto;
      background: none;
      padding: 0;
      border-radius: 0;
      box-shadow: none;
    }
    @media (max-width: 900px) {
      .category-list {
        grid-template-columns: repeat(2, 1fr);
      }
    }
    @media (max-width: 600px) {
      .category-header { flex-direction: column; text-align: center; }
      .category-header h1 { font-size: 1.3em; }
      .category-list { grid-template-columns: 1fr; gap: 12px; }
      .books-grid { grid-template-columns: 1fr; }
      .book-card { flex-direction: column; align-items: center; text-align: center; }
      .book-card img { margin-bottom: 10px; }
      .book-info { align-items: center; }
    }
  </style>
</head>
<body>
  <?php include 'user_sidebar.php'; ?>
  <div class="category-container">
    <div class="category-header">
      <h1><?php echo $selectedCategory ? "Category: " . htmlspecialchars($selectedCategory) : "Browse By Category"; ?></h1>
      <div class="action-buttons" style="display: flex; gap: 16px;">
        <a href="user_dashboard.php"><button>← Back to Dashboard</button></a>
      </div>
    </div>
    <main>
      <?php if (!$selectedCategory): ?>
        <h3>Select a Category:</h3>
        <div class="category-list">
          <?php while ($row = $categoriesResult->fetch_assoc()): ?>
            <a href="category.php?category=<?php echo urlencode($row['category']); ?>">
              <button class="category-button"><?php echo htmlspecialchars($row['category']); ?></button>
            </a>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="books-section">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 10px;">
            <h2 style="margin: 0;">Books in <?php echo htmlspecialchars($selectedCategory); ?></h2>
            <input type="text" id="searchBox" placeholder="Search books..." style="max-width: 320px;">
          </div>
          <?php if ($booksResult->num_rows > 0): ?>
            <div class="books-grid">
              <?php while ($book = $booksResult->fetch_assoc()): ?>
                <div class="book-card" onclick="window.location.href='book_detail.php?id=<?php echo $book['id']; ?>'">
                  <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover">
                  <div class="book-info">
                    <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
                    <div class="book-author">by <?php echo htmlspecialchars($book['author']); ?></div>
                    <div class="book-meta">
                      <span class="book-copies"><?php echo (int)$book['copies']; ?> copies</span>
                    </div>
                  </div>
                </div>
              <?php endwhile; ?>
            </div>
          <?php else: ?>
            <p>No books found in this category.</p>
          <?php endif; ?>
          <div class="category-nav-btns">
            <a href="category.php"><button>Back to Browse By Categories</button></a>
          </div>
        </div>
      <?php endif; ?>
    </main>
  </div>
  <script src="script.js"></script>
</body>
</html>
