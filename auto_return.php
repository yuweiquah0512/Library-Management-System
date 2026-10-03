<?php
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get current time
$now = date('Y-m-d H:i:s');

// Find all borrow_requests not returned and past due date
$sql = "UPDATE borrow_requests 
        SET returned = 1, returned_date = '$now' 
        WHERE returned = 0 AND due_date < '$now'";
$conn->query($sql);

echo "Auto return completed.";
?>