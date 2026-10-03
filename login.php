<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'library_db');

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['reset_password'])) {
  $email = $conn->real_escape_string($_POST['reset_email']);
  $new_password = $_POST['reset_new_password'];
  $confirm_password = $_POST['reset_confirm_password'];

  // Check if passwords match
  if ($new_password !== $confirm_password) {
    echo "
      <script>
        alert('Passwords do not match. Password not changed.');
        window.location.href = 'Main.html';
      </script>
    ";
    exit();
  }

  // Check if email exists
  $check = $conn->query("SELECT * FROM users WHERE email='$email'");
  if ($check->num_rows === 1) {
    // Update password (using SHA1 to match your current logic)
    $conn->query("UPDATE users SET password=SHA1('$new_password') WHERE email='$email'");
    echo "
      <script>
        alert('Password reset successful. Please login with your new password.');
        window.location.href = 'Main.html';
      </script>
    ";
    exit();
  } else {
    echo "
      <script>
        alert('Email not found. Password not changed.');
        window.location.href = 'Main.html';
      </script>
    ";
    exit();
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = $conn->real_escape_string($_POST['username']);
  $password = $_POST['password'];
  $role = isset($_POST['role']) ? $conn->real_escape_string($_POST['role']) : 'user';

  $sql = "SELECT * FROM users WHERE username='$username' AND password=SHA1('$password')";
  $result = $conn->query($sql);

  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if ($user['role'] === $role) {
      $_SESSION['username'] = $username;
      $_SESSION['role'] = $role;
      if ($role === 'admin') {
        echo "
          <script>
            alert('Login Successful as Admin');
            window.location.href = 'admin_dashboard.php';
          </script>
        ";
      } else {
        echo "
          <script>
            alert('Login Successful as User');
            window.location.href = 'user_dashboard.php';
          </script>
        ";
      }
    } else {
      echo "
        <script>
          alert('Role mismatch. Please select the correct role.');
          window.history.back();
        </script>
      ";
    }
  } else {
    echo "
      <script>
        alert('Invalid username or password.');
        window.history.back();
      </script>
    ";
  }
}
?>
