<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'library_db');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM books WHERE id = $delete_id");
    header("Location: manage_book.php");
    exit();
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $title = $conn->real_escape_string($_POST['title']);
    $author = $conn->real_escape_string($_POST['author']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $copies = intval($_POST['copies']);
    
    // Handle category selection (including new category)
    $category = ($_POST['category'] === "Add New" && !empty($_POST['new_category'])) 
                ? $conn->real_escape_string($_POST['new_category']) 
                : $conn->real_escape_string($_POST['category']);
    
    $publish_date = $conn->real_escape_string($_POST['publish_date']);
    $language = $conn->real_escape_string($_POST['language']);
    $cover_image_path = $_POST['existing_cover_image'];

    // Handle new image upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "cover/image/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid("book_", true) . "." . $file_ext;
        $cover_image_path = $upload_dir . $file_name;
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_image_path);
    }

    $sql = "UPDATE books SET 
        title='$title', author='$author', isbn='$isbn', copies=$copies, 
        category='$category', publish_date='$publish_date', language='$language', 
        cover_image='$cover_image_path'
        WHERE id=$id";
    $conn->query($sql);
    echo "<script>alert('Book updated successfully!');window.location='manage_book.php';</script>";
    exit();
}

// --- Search, Filter Logic ---
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_category = isset($_GET['filter_category']) ? $conn->real_escape_string($_GET['filter_category']) : '';

// Build WHERE clause for search and filter
$where = [];
if ($search !== '') {
    $where[] = "(title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%')";
}

if ($filter_category !== '') {
    $where[] = "category = '$filter_category'";
}
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Sorting logic
$sortable = ['title', 'author', 'category', 'copies'];
$sort = isset($_GET['sort']) && in_array($_GET['sort'], $sortable) ? $_GET['sort'] : 'title';

// Fetch all books with search, sort, filter
$sql = "SELECT * FROM books $where_sql";
$result = $conn->query("SELECT * FROM books $where_sql ORDER BY $sort");

// Get all unique categories for dropdown (sorted alphabetically)
$categories = [];
$cat_result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category ASC");
if ($cat_result && $cat_result->num_rows > 0) {
    while ($cat_row = $cat_result->fetch_assoc()) {
        $categories[] = $cat_row['category'];
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Books</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="admin_sidebar.css">
    <style>
        .edit-form { background: #222; padding: 16px; margin-bottom: 24px; border-radius: 8px; }
        .edit-form input, .edit-form select { margin-bottom: 8px; }
        .edit-form img { display: block; margin-bottom: 8px; }
        .book-list-table { width: 100%; border-collapse: collapse; }
        .book-list-table th, .book-list-table td { padding: 8px; border: 1px solid #444; }
        #new-category-span { display: none; }
        .search-bar { margin-bottom: 20px; }
    </style>
    <script>
    function showNewCategoryInput(select) {
        var span = document.getElementById('new-category-span');
        if (select.value === "Add New") {
            span.style.display = "inline";
            document.getElementById('new-category-input').required = true;
        } else {
            span.style.display = "none";
            document.getElementById('new-category-input').required = false;
        }
    }
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
        <h2 style="font-size:2.2em; font-weight:700; background:linear-gradient(135deg,#5f9ea0 0%,#7fb3b5 50%,#64b5f6 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0;animation:glow 2s ease-in-out infinite alternate;">Manage Books</h2>
        <div class="btn-container" style="display:flex; gap:12px; align-items:center; margin-top:0;">
          <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">← Back To Dashboard</a>
          <a href="manage_user.php" class="btn btn-primary btn-sm">Manage Users</a>
        </div>
      </div>
    </div>
    <!-- Search, Filter Bar -->
    <form method="get" class="search-bar" style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
      <div style="display: flex; align-items: center; gap: 10px; margin-left: 35px;">
        <input type="text" name="search" placeholder="Search by Title / Author" value="<?php echo htmlspecialchars($search); ?>">
        <select name="filter_category">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" <?php if ($filter_category == $cat) echo 'selected'; ?>>
              <?php echo htmlspecialchars($cat); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
        <a href="manage_book.php" style="text-decoration:none; margin-right:60px;"><button type="button" class="btn btn-secondary btn-sm">Reset</button></a>
      </div>
    </form>
    <?php
    // If editing, show the edit form
    if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $edit_result = $conn->query("SELECT * FROM books WHERE id=$edit_id");
    if ($edit_result && $edit_result->num_rows > 0) {
        $book = $edit_result->fetch_assoc();
        ?>
        <div class="form-container" style="max-width: 1300px; width: 90vw; margin: 0 auto 40px auto;">
          <h2 style="text-align:center; margin-bottom: 30px; font-size: 2.5em;">EDIT BOOK</h2>
          <form class="edit-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="update_id" value="<?php echo $book['id']; ?>">
            <div class="form-grid">
              <div class="form-row">
                <div class="form-group">
                  <label for="title">Title</label>
                  <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" required>
                </div>
                <div class="form-group">
                  <label for="author">Author</label>
                  <input type="text" id="author" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" required>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="isbn">ISBN No.</label>
                  <input type="text" id="isbn" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" required>
                </div>
                <div class="form-group">
                  <label for="copies">Copies</label>
                  <input type="number" id="copies" name="copies" value="<?php echo $book['copies']; ?>" min="1" required>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="category-select">Category</label>
                  <select name="category" id="category-select" required onchange="showNewCategoryInput(this)">
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                      <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($cat == $book['category']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                      </option>
                    <?php endforeach; ?>
                    <option value="Add New">Add New...</option>
                  </select>
                </div>
                <div class="form-group" id="new-category-span" style="display:none;">
                  <label for="new-category-input">New Category</label>
                  <input type="text" name="new_category" id="new-category-input" placeholder="Enter new category">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label for="publish_date">Published Date</label>
                  <input type="date" id="publish_date" name="publish_date" value="<?php echo $book['publish_date']; ?>" required>
                </div>
                <div class="form-group">
                  <label for="language">Language</label>
                  <select name="language" id="language" required>
                    <option value="">Select Language</option>
                    <option value="English" <?php echo ($book['language'] == 'English') ? 'selected' : ''; ?>>English</option>
                    <option value="Malay" <?php echo ($book['language'] == 'Malay') ? 'selected' : ''; ?>>Malay</option>
                    <option value="Chinese" <?php echo ($book['language'] == 'Chinese') ? 'selected' : ''; ?>>Chinese</option>
                  </select>
                </div>
              </div>
              <div class="form-group full-width">
                <label>Current Cover Image</label><br>
                <img src="<?php echo htmlspecialchars($book['cover_image']); ?>" alt="Cover" width="80" style="margin-bottom:10px;">
                <input type="hidden" name="existing_cover_image" value="<?php echo htmlspecialchars($book['cover_image']); ?>">
                <div class="file-input-wrapper">
                  <label for="edit-cover-image" class="file-input-label">Choose New Cover Image</label>
                  <input type="file" id="edit-cover-image" name="cover_image" accept="image/*" class="file-input-hidden">
                </div>
              </div>
              <div class="form-group full-width btn-container">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="manage_book.php" style="text-decoration:none;"><button type="button" class="btn btn-secondary">Cancel</button></a>
              </div>
            </div>
          </form>
        </div>
        <script>
          // Show/hide new category input if needed (for edit)
          document.addEventListener('DOMContentLoaded', function() {
            var select = document.getElementById('category-select');
            var span = document.getElementById('new-category-span');
            var input = document.getElementById('new-category-input');
            if (select.value === "Add New") {
              span.style.display = "inline";
              input.required = true;
            }
            select.addEventListener('change', function() {
              if (select.value === "Add New") {
                span.style.display = "inline";
                input.required = true;
              } else {
                span.style.display = "none";
                input.required = false;
              }
            });
          });
        </script>
        <?php
    }
}

    // Book list with edit links
    if ($result && $result->num_rows > 0) {
        echo '<div class="book-list-cards" style="display: flex; flex-wrap: wrap; gap: 30px; margin-left: 60px;">';
        while ($row = $result->fetch_assoc()) {
            echo '<div class="book-card" style="display: flex; background: #232b2b; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.12); width: 540px; min-height: 160px; overflow: hidden; position: relative;">';
            // Cover
            echo '<div style="flex: 0 0 120px; display: flex; align-items: center; justify-content: center; background: #181818;">';
            echo '<img src="'.htmlspecialchars($row['cover_image']).'" alt="Cover" style="width: 90px; height: 130px; object-fit: cover; border-radius: 10px; margin: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.18);">';
            echo '</div>';
            // Info
            echo '<div style="flex: 1; padding: 18px 18px 48px 18px; display: flex; flex-direction: column; justify-content: flex-start;">';
            echo '<div style="font-size: 1.2em; font-weight: bold; color: #5f9ea0; margin-bottom: 4px;">'.htmlspecialchars($row['title']).'</div>';
            echo '<div style="margin-bottom: 2px; color: #e0e0e0;"><b>Author:</b> '.htmlspecialchars($row['author']).'</div>';
            echo '<div style="margin-bottom: 2px; color: #e0e0e0;"><b>ISBN No.:</b> '.htmlspecialchars($row['isbn']).'</div>';
            echo '<div style="margin-bottom: 2px; color: #e0e0e0;"><b>Copies:</b> '.(int)$row['copies'].'</div>';
            echo '<div style="margin-bottom: 2px; color: #e0e0e0;"><b>Category:</b> '.htmlspecialchars($row['category']).'</div>';
            echo '<div style="margin-bottom: 2px; color: #e0e0e0;"><b>Published Date:</b> '.htmlspecialchars($row['publish_date']).'</div>';
            echo '<div style="margin-bottom: 2px; color: #e0e0e0;"><b>Language:</b> '.htmlspecialchars($row['language']).'</div>';
            // Edit button
            echo '<a href="manage_book.php?edit_id='.(int)$row['id'].'"><button style="margin-top: 8px; margin-bottom: 8px; background: #5f9ea0; color: #1a1a1a; border: none; border-radius: 6px; padding: 7px 16px; font-weight: bold; font-size: 15px; cursor: pointer; font-family: \'Times New Roman\', Times, serif;">Edit</button></a>';
            // Delete button
            echo '<form method="get" action="" style="position: absolute; bottom: 16px; right: 16px; display: inline;" onsubmit="return confirm(\'Are you sure you want to delete this book?\');">';
            echo '<input type="hidden" name="delete_id" value="'.(int)$row['id'].'">';
            echo '<button type="submit" style="color: #fff; background: #c0392b; border: none; border-radius: 6px; padding: 8px 18px; font-weight: bold; font-size: 15px; cursor: pointer; box-shadow: 0 2px 8px rgba(192,57,43,0.18); font-family: \'Times New Roman\', Times, serif;">Delete</button>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo "<p>No books found.</p>";
    }
    ?>
</body>
</html>