<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get user's name
$username = $_SESSION['username'];
$user = $conn->query("SELECT name FROM users WHERE username='$username'")->fetch_assoc();
$name = $user ? $user['name'] : '';

// Handle suggestion submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);

    if ($title == "") {
        $msg = "Title is required!";
    } else {
        // Insert with status 'in progress'
        $stmt = $conn->prepare("INSERT INTO book_suggestions (username, name, title, author, status) VALUES (?, ?, ?, ?, 'in progress')");
        $stmt->bind_param("ssss", $username, $name, $title, $author);
        $stmt->execute();
        $msg = "Thank you for your suggestion!";
        $stmt->close();
    }
}

// Fetch user's suggestion history (no search)
$history = $conn->query("SELECT * FROM book_suggestions WHERE username='$username' ORDER BY suggested_at ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Recommendations</title>
    <link rel="stylesheet" href="design.css">
    <link rel="stylesheet" href="user_sidebar.css">
    <style>
        .recommendations-container {
            max-width: 600px;
            margin: 48px auto 32px auto;
            background: rgba(42, 42, 66, 0.98);
            border-radius: 20px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.25), 0 1px 0 rgba(95,158,160,0.08);
            border: 1.5px solid rgba(95, 158, 160, 0.18);
            padding: 36px 32px 32px 32px;
        }
        .recommendations-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }
        .recommendations-header h2 {
            font-size: 1.5em;
            font-weight: 700;
            background: linear-gradient(135deg, #5f9ea0 0%, #7fb3b5 50%, #64b5f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
            animation: glow 2s ease-in-out infinite alternate;
        }
        .recommendations-header .back-btn {
            width: auto;
            min-width: 180px;
            margin: 0;
        }
        .suggestion-form {
            background: none;
            box-shadow: none;
            border-radius: 0;
            padding: 0;
            max-width: 100%;
            margin: 0;
        }
        .suggestion-form label {
            display: block;
            margin-top: 18px;
            color: #b0b0b0;
            font-size: 1em;
        }
        .suggestion-form input[type="text"] {
            width: 100%;
            margin-top: 6px;
            padding: 12px;
            border: 1px solid #5f9ea0;
            border-radius: 8px;
            background: #1a1a1a;
            color: #fff;
            font-size: 1em;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .suggestion-form input[type="text"]:focus {
            border-color: #7fb3b5;
            outline: none;
            background: #232b2b;
            box-shadow: 0 0 10px rgba(95, 158, 160, 0.2);
        }
        .suggestion-form button {
            margin-top: 24px;
            width: 100%;
        }
        .msg {
            color: #5f9ea0;
            margin-bottom: 10px;
            font-weight: 600;
            text-align: center;
        }
        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin: 40px auto 0 auto;
            background: #2a2a2a;
            border-radius: 10px;
            border: 1px solid #5f9ea0;
            overflow: hidden;
        }
        .history-table caption {
            caption-side: top;
            font-weight: bold;
            margin-bottom: 8px;
            color: #5f9ea0;
            font-size: 1.1em;
        }
        .history-table th, .history-table td {
            padding: 14px 10px;
            border: 1px solid #5f9ea0;
            text-align: left;
            color: #e0e0e0;
        }
        .history-table th {
            background: #1a1a1a;
            color: #5f9ea0;
            font-weight: 600;
        }
        .status-inprogress { color: #ffc107; font-weight: bold; }
        .status-added { color: #28a745; font-weight: bold; }
        .status-removed { color: #dc3545; font-weight: bold; }
        @media (max-width: 700px) {
            .recommendations-container { padding: 12px 2vw; }
        }
    </style>
</head>
<body>
    <?php include 'user_sidebar.php'; ?>
    <div class="recommendations-container">
        <div class="recommendations-header">
            <h2>Suggest a New Book</h2>
            <a href="user_dashboard.php"><button type="button" class="back-btn">← Back to Dashboard</button></a>
        </div>
        <?php if (!empty($msg)) echo "<div class='msg'>".htmlspecialchars($msg)."</div>"; ?>
        <form method="POST" action="user_recommendations.php" class="suggestion-form">
            <label>Title:
                <input type="text" name="title" required>
            </label>
            <label>Author:
                <input type="text" name="author" placeholder="(Optional)">
            </label>
            <button type="submit">Submit Suggestion</button>
        </form>
        <?php if ($history && $history->num_rows > 0): ?>
        <table class="history-table">
            <caption>Your Book Suggestions</caption>
            <tr>
                <th>No.</th>
                <th>Title</th>
                <th>Author</th>
                <th>Suggested At</th>
                <th>Status</th>
            </tr>
            <?php $i = 1; while($row = $history->fetch_assoc()): ?>
            <tr>
                <td><?php echo $i++; ?></td>
                <td><?php echo htmlspecialchars($row['title']); ?></td>
                <td><?php echo htmlspecialchars($row['author']); ?></td>
                <td><?php echo htmlspecialchars($row['suggested_at']); ?></td>
                <td>
                    <?php if ($row['status'] === 'added'): ?>
                        <span class="status-added">Book Added</span>
                    <?php elseif ($row['status'] === 'removed'): ?>
                        <span class="status-removed">Book Not Added</span>
                    <?php else: ?>
                        <span class="status-inprogress">In Progress</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        <?php endif; ?>
    </div>
</body>
</html>