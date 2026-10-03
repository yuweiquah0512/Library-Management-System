<?php
session_start();
date_default_timezone_set('Asia/Singapore'); 

if (!isset($_SESSION['username']) || !isset($_GET['request_id']) || !isset($_GET['book_id'])) {
  die("Invalid return request.");
}

$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$request_id = (int)$_GET['request_id'];
$book_id = (int)$_GET['book_id'];
$username = $_SESSION['username'];

// 1. Check that the user owns this borrow and hasn't returned it yet
$check = $conn->query("SELECT * FROM borrow_requests WHERE id = $request_id AND book_id = $book_id AND username = '$username' AND returned = 0");

if ($check->num_rows === 0) {
  echo "<script>alert('Invalid or already returned book.'); window.location.href = 'borrowed_books.php';</script>";
  exit;
}

// 2. Mark as returned and set returned_date
$now = date('Y-m-d H:i:s');
$conn->query("UPDATE borrow_requests SET returned = 1, returned_date = '$now' WHERE id = $request_id");

// 3. Increment book copies
$conn->query("UPDATE books SET copies = copies + 1 WHERE id = $book_id");

// 4. Confirm
echo "<script>alert('Book successfully returned!'); window.location.href = 'borrowed_books.php';</script>";
?>