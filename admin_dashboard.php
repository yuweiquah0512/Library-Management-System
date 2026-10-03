<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Library</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="admin_sidebar.css">
</head>
<body>
    <?php include 'admin_sidebar.php'; ?>
    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="header">
            <div class="header-content">
                <div class="welcome-section">
                    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                    <p>
                        <i class="fas fa-user-shield"></i>
                        Welcome back, <strong><?php echo isset($_SESSION['name']) ? $_SESSION['name'] : 'Admin'; ?></strong>
                    </p>
                </div>
                <a href="Main.html" class="logout-btn" onclick="return confirm('Are you sure you want to logout?');">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            <div class="dashboard-card" onclick="location.href='add_book.php'">
                <div class="card-icon">
                    <i class="fas fa-plus-circle"></i>
                </div>
                <div class="card-content">
                    <h3>Add New Books</h3>
                    <p>Expand the library collection by adding new books to the system.</p>
                    <a href="add_book.php" class="card-link">
                        Add Books <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="dashboard-card" onclick="location.href='borrow.php'">
                <div class="card-icon">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div class="card-content">
                    <h3>Borrowed Books</h3>
                    <p>Monitor all currently borrowed books, track due dates and manage overdue items.</p>
                    <a href="borrow.php" class="card-link">
                        View Borrowed <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="dashboard-card" onclick="location.href='return.php'">
                <div class="card-icon">
                    <i class="fas fa-undo-alt"></i>
                </div>
                <div class="card-content">
                    <h3>Returned Books</h3>
                    <p>Process book returns efficiently, view the return date and update availability status.</p>
                    <a href="return.php" class="card-link">
                        View Returned <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="dashboard-card" onclick="location.href='manage_book.php'">
                <div class="card-icon">
                    <i class="fas fa-book-open"></i>
                </div>
                <div class="card-content">
                    <h3>Manage Books</h3>
                    <p>View, edit and organize the entire book collection by updating the latest book information.</p>
                    <a href="manage_book.php" class="card-link">
                        Manage Books <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="dashboard-card" onclick="location.href='manage_user.php'">
                <div class="card-icon">
                   <i class="fas fa-users-cog"></i>
                </div>
                <div class="card-content">
                    <h3>Manage Users</h3>
                    <p>View, edit and manage all users by updating the user details and monitor borrowing activity.</p>
                    <a href="manage_user.php" class="card-link">
                        Manage Users <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

            <div class="dashboard-card" onclick="location.href='admin_recommendations.php'">
                <div class="card-icon">
                    <i class="fas fa-lightbulb"></i>
                </div>
                <div class="card-content">
                    <h3>Recommendations</h3>
                    <p>View and manage books suggested by users. Approve or reject new book recommendations for the library.</p>
                    <a href="admin_recommendations.php" class="card-link">
                        View Recommendations <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
        // Add click ripple effect
        const cards = document.querySelectorAll('.dashboard-card');
        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                const ripple = document.createElement('div');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.background = 'rgba(95, 158, 160, 0.3)';
                ripple.style.transform = 'scale(0)';
                ripple.style.animation = 'ripple 0.6s linear';
                ripple.style.left = (e.clientX - card.offsetLeft) + 'px';
                ripple.style.top = (e.clientY - card.offsetTop) + 'px';
                
                card.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });
    });

    // Add CSS for ripple animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
</script>
</body>
</html>