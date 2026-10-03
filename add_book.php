<?php
$conn = new mysqli('localhost', 'root', '', 'library_db');

// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM books WHERE id = $delete_id");
    header("Location: add_book.php");
    exit();
}

// Function to add a new book
function addBook($conn, $title, $author, $isbn, $copies, $category, $publish_date, $language, $cover_image_path) {
    // Escape input to prevent SQL injection
    $title = $conn->real_escape_string($title);
    $author = $conn->real_escape_string($author);
    $isbn = $conn->real_escape_string($isbn);
    $copies = intval($copies);
    $category = $conn->real_escape_string($category);
    $publish_date = $conn->real_escape_string($publish_date);
    $language = $conn->real_escape_string($language);
    $cover_image_path = $conn->real_escape_string($cover_image_path);

    $sql = "INSERT INTO books (title, author, isbn, copies, category, publish_date, language, cover_image) 
            VALUES ('$title', '$author', '$isbn', $copies, '$category', '$publish_date', '$language', '$cover_image_path')";
    
    if ($conn->query($sql)) {
        return true; // Success
    } else {
        return false; // Error
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Book</title>
  <link rel="stylesheet" href="admin.css">
  <link rel="stylesheet" href="admin_sidebar.css">
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <br>
    <div class="header" style="margin: 12px 30px 18px 30px; padding: 16px 24px; border-radius: 14px;">
      <div class="header-content" style="display:flex; align-items:center; justify-content:space-between; gap:10px;">
        <h2 style="font-size:2.2em; font-weight:700; background:linear-gradient(135deg,#5f9ea0 0%,#7fb3b5 50%,#64b5f6 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0;animation:glow 2s ease-in-out infinite alternate;">Add New Book</h2>
        <div class="btn-container" style="display:flex; gap:12px; align-items:center; margin-top:0;">
          <a href="admin_dashboard.php" class="btn btn-secondary btn-sm">← Back To Dashboard</a>
          <a href="admin_recommendations.php" class="btn btn-primary btn-sm" style="color:#222;">View Recommendations</a>
        </div>
      </div>
    </div>
    <br>
    <div class="form-container" style="max-width: 1300px; width: 90vw; margin: 0 auto 40px auto;">
      <h2 style="text-align:center; margin-bottom: 30px; font-size: 2.5em;">ADD A NEW BOOK</h2>
      <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-grid">
          <div class="form-row">
            <div class="form-group">
              <label for="title">Title</label>
              <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
              <label for="author">Author</label>
              <input type="text" id="author" name="author" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="isbn">ISBN No.</label>
              <input type="text" id="isbn" name="isbn" required>
            </div>
            <div class="form-group">
              <label for="copies">Copies</label>
              <input type="number" id="copies" name="copies" min="1" required>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="category-select">Category</label>
              <select name="category" id="category-select" required onchange="showNewCategoryInput(this)">
                <option value="">Select Category</option>
                <?php
                $categories = [];
                $cat_result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category ASC");
                if ($cat_result && $cat_result->num_rows > 0) {
                  while ($cat_row = $cat_result->fetch_assoc()) {
                    echo "<option value='{$cat_row['category']}'>{$cat_row['category']}</option>";
                  }
                }
                ?>
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
              <label for="publish_date">Publish Date</label>
              <input type="date" id="publish_date" name="publish_date" required>
            </div>
            <div class="form-group">
              <label for="language">Language</label>
              <select name="language" id="language" required>
                <option value="">Select Language</option>
                <option value="English">English</option>
                <option value="Malay">Malay</option>
                <option value="Chinese">Chinese</option>
              </select>
            </div>
          </div>
          <div class="form-group full-width">
            <label for="cover_image">Cover Image</label>
            <div class="file-input-wrapper">
              <label for="cover_image" class="file-input-label">Choose Cover Image</label>
              <input type="file" id="cover_image" name="cover_image" accept="image/*" required class="file-input-hidden">
            </div>
          </div>
          <div class="form-group full-width btn-container">
            <button type="submit" class="btn btn-primary">Add Book</button>
          </div>
        </div>
      </form>
    </div>
    <!-- Search, Filter Bar (place above book list table) -->
    <form method="get" class="search-bar" style="margin: 20px 0; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <input type="text" name="search" placeholder="Search by Title / Author / ISBN No." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="width:300px; margin-left: 60px;">
        <select name="filter_category" style="width:250px;">
          <option value="">All Categories</option>
          <?php
          // Get unique categories for filter dropdown
          $cat_result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category ASC");
          if ($cat_result && $cat_result->num_rows > 0) {
            while ($cat_row = $cat_result->fetch_assoc()) {
              $selected = (isset($_GET['filter_category']) && $_GET['filter_category'] == $cat_row['category']) ? 'selected' : '';
              echo "<option value='".htmlspecialchars($cat_row['category'])."' $selected>".htmlspecialchars($cat_row['category'])."</option>";
            }
          }
          ?>
        </select>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <button type="submit" class="btn btn-primary btn-sm" style="font-family: 'Times New Roman', Times, serif;">Search</button>
        <a href="add_book.php" style="text-decoration:none; margin-right:60px;"><button type="button" class="btn btn-secondary btn-sm" style="font-family: 'Times New Roman', Times, serif;">Reset</button></a>
      </div>
    </form>
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
    </script>
<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $isbn = $_POST['isbn'] ?? '';
    $copies = $_POST['copies'] ?? 0;
    $category = ($_POST['category'] === "Add New" && !empty($_POST['new_category'])) 
                ? $_POST['new_category'] 
                : $_POST['category'];
    $publish_date = $_POST['publish_date'] ?? '';
    $language = $_POST['language'] ?? '';

    $cover_image_path = '';
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = "cover/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
        $file_name = uniqid("book_", true) . "." . $file_ext;
        $cover_image_path = $upload_dir . $file_name;
        move_uploaded_file($_FILES['cover_image']['tmp_name'], $cover_image_path);
    }

    if (addBook($conn, $title, $author, $isbn, $copies, $category, $publish_date, $language, $cover_image_path)) {
        echo "<p>Book added successfully.</p>";
    } else {
        echo "<p>Error: " . $conn->error . "</p>";
    }
}

// Handle search/filter logic for book list
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_category = isset($_GET['filter_category']) ? $conn->real_escape_string($_GET['filter_category']) : '';
$where = [];
if ($search !== '') {
    $where[] = "(title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%')";
}
if ($filter_category !== '') {
    $where[] = "category = '$filter_category'";
}
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Display all books with search/filter
$result = $conn->query("SELECT * FROM books $where_sql");
if ($result && $result->num_rows > 0) {
    echo '<div class="book-list-cards" style="display: flex; flex-wrap: wrap; gap: 60px; margin-left: 60px; margin-bottom: 30px;">';
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
        echo '<div style="margin-bottom: 2px; color: #e0e0e0;"><b>Publish Date:</b> '.htmlspecialchars($row['publish_date']).'</div>';
        echo '<div style="margin-bottom: 2px; color: #e0e0e0;"><b>Language:</b> '.htmlspecialchars($row['language']).'</div>';
        // Delete button
        echo '<form method="get" action="" style="position: absolute; bottom: 16px; right: 16px; display: inline;" onsubmit="return confirm(\'Are you sure you want to delete this book?\');">';
        echo '<input type="hidden" name="delete_id" value="'.(int)$row['id'].'">';
        echo '<button type="submit" style="color: #fff; background: #c0392b; border: none; border-radius: 6px; padding: 8px 18px; font-weight: bold; font-size: 15px; cursor: pointer; box-shadow: 0 2px 8px rgba(192,57,43,0.18);">Delete</button>';
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