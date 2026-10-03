<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_POST['book_id']) || !isset($_POST['book_title']) || !isset($_POST['rent_duration'])) {
  die("Invalid request.");
}

$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];
$book_id = (int)$_POST['book_id'];
$book_title = $conn->real_escape_string($_POST['book_title']);
$rent_duration = (int)$_POST['rent_duration'];

// 1. Check if user already borrowed this book and not returned it yet
$already = $conn->query("SELECT 1 FROM borrow_requests WHERE username='$username' AND book_id=$book_id AND returned=0");
if ($already->num_rows > 0) {
  echo "<script>alert('❌ You already borrowed this book and have not returned it yet.'); window.history.back();</script>";
  exit;
}

// 2. Check if user has reached the maximum borrow limit (5)
$borrowed_count = $conn->query("SELECT COUNT(*) AS cnt FROM borrow_requests WHERE username='$username' AND returned=0")->fetch_assoc()['cnt'];
if ($borrowed_count >= 5) {
  echo "<script>alert('❌ You have reached the maximum borrow limit (5 books). Please return a book before borrowing another.'); window.history.back();</script>";
  exit;
}

// 3. Check available copies
$check = $conn->query("SELECT copies FROM books WHERE id = $book_id");
if ($check->num_rows === 0 || (int)$check->fetch_assoc()['copies'] <= 0) {
  echo "<script>alert('❌ This book is not available.'); window.history.back();</script>";
  exit;
}

// 4. Calculate due date
$due_date = date('Y-m-d H:i:s', strtotime("+$rent_duration days"));

// 5. Insert into borrow_requests
$insert = "INSERT INTO borrow_requests (username, book_id, book_title, rent_duration, due_date)
           VALUES ('$username', $book_id, '$book_title', $rent_duration, '$due_date')";
$conn->query($insert);

// 6. Reduce available copies
$conn->query("UPDATE books SET copies = copies - 1 WHERE id = $book_id");

// 7. Confirm success
echo "
  <script>
    alert('✅ Borrow request submitted! Due date: $due_date');
    window.location.href = 'user_dashboard.php';
  </script>
";
?>