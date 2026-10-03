<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_id'])) {
    $id = intval($_POST['edit_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $conn->query("UPDATE users SET name='$name', username='$username', email='$email' WHERE id=$id");
    echo "<script>alert('User updated successfully!');window.location='manage_user.php';</script>";
    exit();
}

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM users WHERE id=$delete_id");
    echo "<script>alert('User deleted successfully!');window.location='manage_user.php';</script>";
    exit();
}

// --- Search, Sort, Filter Logic ---
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_role = isset($_GET['filter_role']) ? $conn->real_escape_string($_GET['filter_role']) : '';
$sort = isset($_GET['sort']) ? $conn->real_escape_string($_GET['sort']) : 'id';
$order = isset($_GET['order']) && strtolower($_GET['order']) === 'desc' ? 'DESC' : 'ASC';

// Build WHERE clause for search and filter
$where = ["role != 'admin'"];
if ($search !== '') {
    $where[] = "(name LIKE '%$search%' OR username LIKE '%$search%' OR email LIKE '%$search%')";
}
if ($filter_role !== '') {
    $where[] = "role = '$filter_role'";
}
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Validate sort column
$sortable = ['id','name','username','email','book_borrowed','book_returned','book_borrowing'];
if (!in_array($sort, $sortable)) $sort = 'id';

// Build SELECT with subqueries for sorting by book stats
$select = "SELECT users.*,
    (SELECT COUNT(*) FROM borrow_requests WHERE username=users.username) AS book_borrowed,
    (SELECT COUNT(*) FROM borrow_requests WHERE username=users.username AND returned=1) AS book_returned,
    (SELECT COUNT(*) FROM borrow_requests WHERE username=users.username AND returned=0) AS book_borrowing
    FROM users $where_sql";

// Sorting by computed columns if needed
if (in_array($sort, ['book_borrowed','book_returned','book_borrowing'])) {
    $select .= " ORDER BY $sort $order";
} else {
    $select .= " ORDER BY users.$sort $order";
}

$users = $conn->query($select);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="admin_sidebar.css">
    <style>
        .edit-form { background: #222; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
        .edit-form input { margin-bottom: 6px; }
        .user-list-container {
            max-width: 1200px;
            margin: 32px auto 0 auto;
            background: rgba(42, 42, 66, 0.98);
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.18), 0 1px 0 rgba(95,158,160,0.08);
            border: 1.5px solid rgba(95, 158, 160, 0.18);
            padding: 28px 18px 18px 18px;
        }
        .user-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: none;
            overflow: hidden;
            margin: 0;
        }
        .user-table th, .user-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #5f9ea0;
            text-align: left;
            color: #e0e0e0;
            font-size: 1em;
        }
        .user-table th {
            background: #1a1a1a;
            color: #5f9ea0;
            font-weight: 700;
            border-top: none;
        }
        .user-table tr:last-child td {
            border-bottom: none;
        }
        .user-table tr {
            transition: background 0.2s;
        }
        .user-table tbody tr:hover td {
            background: #232b3b;
        }
        .user-table td {
            vertical-align: middle;
        }
        .user-action-btn {
            font-family: 'Times New Roman', Times, serif;
            font-size: 1em;
            font-weight: 600;
            border: none;
            background: none;
            color: #1976d2;
            padding: 0;
            margin: 0 10px 0 0;
            box-shadow: none;
            cursor: pointer;
            text-decoration: underline;
            transition: color 0.2s;
            display: inline;
        }
        .user-action-btn:hover {
            color: #0d47a1;
            text-decoration: underline;
        }
        .user-delete-btn {
            color: #c0392b;
            background: none;
            box-shadow: none;
        }
        .user-delete-btn:hover {
            color: #a93226;
            text-decoration: underline;
        }
        @media (max-width: 900px) {
            .user-list-container { padding: 8px 2vw; }
            .user-table th, .user-table td { padding: 10px 4px; font-size: 0.95em; }
        }
        .search-bar-container {
            max-width: 1200px;
            margin: 0 auto 24px auto;
            width: 100%;
        }
        .search-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            gap: 10px;
        }
        .search-bar input[type="text"] {
            flex: 1 1 0%;
            min-width: 0;
            font-size: 1em;
            padding: 14px 16px;
        }
        .search-bar button,
        .search-bar a > button {
            min-width: 140px;
            font-size: 1em;
            padding: 14px 0;
        }
    </style>
    <script>
    function sortTable(column) {
        const url = new URL(window.location.href);
        let order = url.searchParams.get('order') === 'ASC' ? 'DESC' : 'ASC';
        url.searchParams.set('sort', column);
        url.searchParams.set('order', order);
        window.location.href = url.toString();
    }
    </script>
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="header" style="margin: 12px 30px 18px 30px; padding: 16px 24px; border-radius: 14px;">
      <div class="header-content" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <h2 style="font-size:2.2em; font-weight:700; background:linear-gradient(135deg,#5f9ea0 0%,#7fb3b5 50%,#64b5f6 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0;animation:glow 2s ease-in-out infinite alternate;">Manage Users</h2>
        <div class="btn-container" style="display:flex; gap:12px; align-items:center; margin-top:0;">
          <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">← Back To Dashboard</a>
          <a href="manage_book.php" class="btn btn-primary btn-sm">Manage Books</a>
        </div>
      </div>
    </div>

    <!-- Search, Filter Bar -->
    <div class="search-bar-container">
      <form method="get" class="search-bar">
        <input type="text" name="search" placeholder="Search Name / Username / Email" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
        <a href="manage_user.php" style="text-decoration:none;"><button type="button" class="btn btn-secondary btn-sm">Reset</button></a>
      </form>
    </div>

    <div class="user-list-container">
    <?php
    if ($users && $users->num_rows > 0) {
        echo "<table class='user-table'><thead><tr>
            <th style='cursor:pointer' onclick=\"sortTable('name')\">Name</th>
            <th style='cursor:pointer' onclick=\"sortTable('username')\">Username</th>
            <th style='cursor:pointer' onclick=\"sortTable('email')\">Email</th>
            <th style='cursor:pointer' onclick=\"sortTable('book_borrowed')\">Books Borrowed</th>
            <th style='cursor:pointer' onclick=\"sortTable('book_returned')\">Books Returned</th>
            <th style='cursor:pointer' onclick=\"sortTable('book_borrowing')\">Books Borrowing</th>
            <th>Action</th>
        </tr></thead><tbody>";
        while ($user = $users->fetch_assoc()) {
            $borrowed = $user['book_borrowed'];
            $returned = $user['book_returned'];
            $borrowing = $user['book_borrowing'];

            if (isset($_GET['edit_id']) && $_GET['edit_id'] == $user['id']) {
                echo "<form class='edit-form' method='POST'><tr>
                        <input type='hidden' name='edit_id' value='{$user['id']}'>
                        <td><input type='text' name='name' value=\"".htmlspecialchars($user['name'])."\" required></td>
                        <td><input type='text' name='username' value=\"".htmlspecialchars($user['username'])."\" required></td>
                        <td><input type='email' name='email' value=\"".htmlspecialchars($user['email'])."\" required></td>
                        <td>$borrowed</td>
                        <td>$returned</td>
                        <td>$borrowing</td>
                        <td>
                            <button type='submit' class='user-action-btn'>Save</button>
                            <a href='manage_user.php'><button type='button' class='user-action-btn'>Cancel</button></a>
                        </td>
                    </tr></form>";
            }else {
                echo "<tr>
                    <td>{$user['name']}</td>
                    <td>{$user['username']}</td>
                    <td>{$user['email']}</td>
                    <td>$borrowed</td>
                    <td>$returned</td>
                    <td>$borrowing</td>
                    <td>
                        <a href='manage_user.php?edit_id={$user['id']}' class='user-action-btn'>Edit</a>
                        <form method='get' action='' style='display:inline;' onsubmit=\"return confirm('Are you sure you want to delete this user?');\">
                            <input type='hidden' name='delete_id' value='{$user['id']}'>
                            <button type='submit' class='user-action-btn user-delete-btn'>Delete</button>
                        </form>
                    </td>
                </tr>";
            }
        }
        echo "</tbody></table>";
    } else {
        echo "<p>No users found.</p>";
    }
    ?>
    </div>
</body>
</html>