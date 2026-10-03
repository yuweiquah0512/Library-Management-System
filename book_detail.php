<?php
session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  die("Invalid book ID.");
}

$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$id = (int) $_GET['id'];
$result = $conn->query("SELECT * FROM books WHERE id = $id");

if ($result->num_rows === 0) {
  die("Book not found.");
}

$book = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Book Details</title>
  <link rel="stylesheet" href="design.css">
  <link rel="stylesheet" href="admin.css">
  <style>
    body {
      background: linear-gradient(135deg, #0f0f23 0%, #1a1a3e 50%, #0f0f23 100%);
      min-height: 100vh;
      color: #e0e0e0;
    }
    .book-detail-container {
      max-width: 600px;
      margin: 40px auto;
      background: rgba(42, 42, 66, 0.97);
      border-radius: 24px;
      box-shadow: 0 16px 48px rgba(0,0,0,0.25), 0 1px 0 rgba(95,158,160,0.08);
      border: 1.5px solid rgba(95, 158, 160, 0.22);
      padding: 40px 32px 32px 32px;
      display: flex;
      flex-direction: column;
      align-items: center;
      position: relative;
    }
    .book-detail-header {
      width: 100%;
      text-align: center;
      margin-bottom: 24px;
    }
    .book-detail-header h1 {
      font-size: 2.1em;
      font-weight: 700;
      background: linear-gradient(135deg, #5f9ea0 0%, #7fb3b5 50%, #64b5f6 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin: 0 0 8px 0;
      animation: glow 2s ease-in-out infinite alternate;
    }
    .book-cover {
      border-radius: 16px;
      box-shadow: 0 8px 32px rgba(95,158,160,0.13), 0 2px 8px rgba(0,0,0,0.18);
      margin-bottom: 18px;
      background: #232b2b;
      object-fit: cover;
      width: 180px;
      height: 260px;
      display: block;
    }
    .book-title {
      font-size: 1.4em;
      font-weight: 700;
      color: #5f9ea0;
      margin-bottom: 8px;
      text-align: center;
    }
    .book-meta {
      color: #b0b0b0;
      font-size: 1.05em;
      margin-bottom: 18px;
      text-align: center;
    }
    .book-detail-list {
      width: 100%;
      margin-bottom: 24px;
    }
    .book-detail-list p {
      margin: 8px 0;
      font-size: 1.08em;
      color: #e0e0e0;
      line-height: 1.5;
    }
    .book-detail-list strong {
      color: #7fb3b5;
      font-weight: 600;
    }
    .book-actions {
      display: flex;
      gap: 16px;
      width: 100%;
      justify-content: center;
      margin-top: 18px;
    }
    .book-actions button, .book-actions a > button {
      background: #5f9ea0;
      color: #1a1a1a;
      padding: 14px 28px;
      border: none;
      border-radius: 10px;
      font-weight: 600;
      font-size: 1em;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(95, 158, 160, 0.18);
      transition: all 0.3s;
    }
    .book-actions button:hover, .book-actions a > button:hover {
      background: #7fb3b5;
      color: #232b2b;
      box-shadow: 0 8px 25px rgba(95, 158, 160, 0.28);
      transform: translateY(-2px);
    }
    @media (max-width: 700px) {
      .book-detail-container { padding: 18px 6vw; }
      .book-cover { width: 120px; height: 170px; }
    }
  </style>
</head>
<body>
  <div class="book-detail-container">
    <div class="book-detail-header">
      <h1>Book Details</h1>
    </div>
    <img class="book-cover" src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Book Cover">
    <div class="book-title"><?php echo htmlspecialchars($book['title']); ?></div>
    <div class="book-meta">by <?php echo htmlspecialchars($book['author']); ?></div>
    <div class="book-detail-list">
      <p><strong>ISBN:</strong> <?php echo htmlspecialchars($book['isbn']); ?></p>
      <p><strong>Category:</strong> <?php echo htmlspecialchars($book['category']); ?></p>
      <p><strong>Language:</strong> <?php echo htmlspecialchars($book['language']); ?></p>
      <p><strong>Published Date:</strong> <?php echo htmlspecialchars($book['publish_date']); ?></p>
      <p><strong>Copies Available:</strong> <span id="copyCount"><?php echo (int)$book['copies']; ?></span></p>
    </div>
    <div class="book-actions">
      <form method="GET" action="select_duration.php" onsubmit="return checkCopies()" style="display:inline;">
        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
        <input type="hidden" name="book_title" value="<?php echo htmlspecialchars($book['title']); ?>">
        <button type="submit">Request Borrow</button>
      </form>
      <a href="user_dashboard.php" style="display:inline;"><button type="button">← Back to Dashboard</button></a>
    </div>
  </div>

  <script>
    function checkCopies() {
      const copies = parseInt(document.getElementById("copyCount").textContent);
      if (copies <= 0) {
        alert("❌ This book is currently not available.");
        return false;
      }
      return true;
    }
  </script>
</body>
</html>
