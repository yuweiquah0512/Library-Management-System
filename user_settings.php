<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$user = $conn->query("SELECT * FROM users WHERE username='$username'")->fetch_assoc();

// Book stats
$borrowed = $conn->query("SELECT COUNT(*) AS total FROM borrow_requests WHERE username='$username'")->fetch_assoc()['total'];
$returned = $conn->query("SELECT COUNT(*) AS total FROM borrow_requests WHERE username='$username' AND returned=1")->fetch_assoc()['total'];
$borrowing = $conn->query("SELECT COUNT(*) AS total FROM borrow_requests WHERE username='$username' AND returned=0")->fetch_assoc()['total'];

// Handle settings update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_settings'])) {
    $new_name = $conn->real_escape_string($_POST['name']);
    $new_username = $conn->real_escape_string($_POST['username']);
    $new_email = $conn->real_escape_string($_POST['email']);
    $update_password = false;
    if (!empty($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        if ($new_password === $confirm_password) {
            $update_password = true;
        } else {
            echo "<script>alert('Passwords do not match!');</script>";
        }
    }
    // Check if username or email already exists for another user
    $check = $conn->query("SELECT * FROM users WHERE (username='$new_username' OR email='$new_email') AND id != {$user['id']}");
    if ($check->num_rows > 0) {
        echo "<script>alert('Username or email already taken!');</script>";
    } else {
        $sql = "UPDATE users SET name='$new_name', username='$new_username', email='$new_email'";
        if ($update_password) {
            $sql .= ", password=SHA1('$new_password')";
        }
        $sql .= " WHERE id={$user['id']}";
        if ($conn->query($sql)) {
            $_SESSION['username'] = $new_username;
            echo "<script>alert('Settings updated successfully!');window.location='user_dashboard.php';</script>";
            exit();
        } else {
            echo "<script>alert('Failed to update settings.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Settings</title>
    <link rel="stylesheet" href="design.css">
    <link rel="stylesheet" href="user_sidebar.css">
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a3e 50%, #0f0f23 100%);
            min-height: 100vh;
            color: #e0e0e0;
        }
        .settings-container {
            max-width: 440px;
            margin: 48px auto;
            background: rgba(42, 42, 66, 0.97);
            border-radius: 22px;
            box-shadow: 0 12px 36px rgba(0,0,0,0.22), 0 1px 0 rgba(95,158,160,0.08);
            border: 1.5px solid rgba(95, 158, 160, 0.22);
            padding: 36px 32px 28px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        .settings-container h2 {
            margin-top: 0;
            font-size: 1.6em;
            font-weight: 700;
            background: linear-gradient(135deg, #5f9ea0 0%, #7fb3b5 50%, #64b5f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 18px;
            animation: glow 2s ease-in-out infinite alternate;
        }
        .settings-container form {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .settings-container label {
            color: #7fb3b5;
            font-size: 1.05em;
            font-weight: 600;
            margin-top: 8px;
            margin-bottom: 2px;
            letter-spacing: 0.5px;
        }
        .settings-container input[type="text"],
        .settings-container input[type="email"],
        .settings-container input[type="password"] {
            width: 100%;
            margin-top: 2px;
            padding: 12px;
            border: 1.5px solid #5f9ea0;
            border-radius: 8px;
            background: #1a1a1a;
            color: #e0e0e0;
            font-size: 1em;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .settings-container input:focus {
            outline: none;
            border-color: #7fb3b5;
            box-shadow: 0 0 10px rgba(95, 158, 160, 0.2);
            background: #232b2b;
        }
        .settings-container button[type="submit"] {
            background: #5f9ea0;
            color: #1a1a1a;
            padding: 14px 0;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1em;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(95, 158, 160, 0.18);
            margin-top: 18px;
            transition: all 0.3s;
        }
        .settings-container button[type="submit"]:hover {
            background: #7fb3b5;
            color: #232b2b;
            box-shadow: 0 8px 25px rgba(95, 158, 160, 0.28);
            transform: translateY(-2px);
        }
        .back-btn {
            margin-bottom: 16px;
            width: 100%;
            display: flex;
            justify-content: flex-start;
        }
        .back-btn button {
            background: #5f9ea0;
            color: #1a1a1a;
            border: none;
            border-radius: 10px;
            padding: 14px 0;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(95, 158, 160, 0.18);
            width: 100%;
            transition: all 0.3s;
        }
        .back-btn button:hover {
            background: #7fb3b5;
            color: #232b2b;
            box-shadow: 0 8px 25px rgba(95, 158, 160, 0.28);
            transform: translateY(-2px);
        }
        .stats-box {
            color: #e0e0e0;
            margin: 24px 0 0 0;
            padding: 16px;
            background: rgba(95, 158, 160, 0.08);
            border-radius: 10px;
            width: 100%;
            font-size: 1.08em;
            box-shadow: 0 2px 8px rgba(95,158,160,0.08);
            border: 1px solid #5f9ea0;
        }
        .stats-box b {
            color: #7fb3b5;
        }
        @media (max-width: 600px) {
            .settings-container { padding: 14px 4vw; }
        }
    </style>
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    <div class="settings-container">
        <a href="user_dashboard.php" class="back-btn"><button type="button">← Back to Dashboard</button></a>
        <h2>User Settings</h2>
        <form method="POST" action="user_settings.php">
            <label>Name:
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </label>
            <label>Username:
                <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </label>
            <label>Email:
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </label>
            <label>New Password:
                <input type="password" name="new_password" placeholder="Leave blank to keep current password">
            </label>
            <label>Confirm Password:
                <input type="password" name="confirm_password" placeholder="Leave blank to keep current passowrd">
            </label>
            <button type="submit" name="update_settings" value="1">Save Changes</button>
        </form>
        <div class="stats-box">
            <b>Books Borrowed:</b> <?php echo $borrowed; ?><br>
            <b>Books Returned:</b> <?php echo $returned; ?><br>
            <b>Books Borrowing:</b> <?php echo $borrowing; ?>
        </div>
    </div>
</body>
</html>