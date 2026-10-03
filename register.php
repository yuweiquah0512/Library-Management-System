<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'library_db');

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $conn->real_escape_string($_POST['name']);
  $username = $conn->real_escape_string($_POST['username']);
  $email = $conn->real_escape_string($_POST['email']);
  $password = $_POST['password'];
  $confirm_password = $_POST['confirm_password'];
  $role = isset($_POST['role']) ? $conn->real_escape_string($_POST['role']) : 'user';

  // Validation
  $errors = array();

  // Check if username already exists
  $check_username = "SELECT * FROM users WHERE username='$username'";
  $result = $conn->query($check_username);
  if ($result->num_rows > 0) {
    $errors[] = "Username already exists. Please choose a different username.";
  }

  // Check if email already exists
  $check_email = "SELECT * FROM users WHERE email='$email'";
  $result = $conn->query($check_email);
  if ($result->num_rows > 0) {
    $errors[] = "Email already registered. Please use a different email.";
  }

  // Validate password
  if (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long.";
  }

  if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
  }

  // Validate email format
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
  }

  // If no errors, proceed with registration
  if (empty($errors)) {
    $hashed_password = SHA1($password);
    $sql = "INSERT INTO users (name, username, email, password, role) 
            VALUES ('$name', '$username', '$email', '$hashed_password', '$role')";
    
    if ($conn->query($sql) === TRUE) {
      echo "
        <script>
          alert('Registration successful! Please login with your new account.');
          window.location.href = 'Main.html';
        </script>
      ";
    } else {
      echo "
        <script>
          alert('Registration failed: " . $conn->error . "');
          window.history.back();
        </script>
      ";
    }
  } else {
    // Display errors
    $error_message = implode("\\n", $errors);
    echo "
      <script>
        alert('Registration failed:\\n$error_message');
        window.history.back();
      </script>
    ";
  }
}
?>