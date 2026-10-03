<!-- Toggle Button (Right Side) -->
<button id="sidebarToggle" class="sidebar-toggle">
    ☰
</button>

<!-- Sidebar (Right Side) -->
<div id="adminSidebar" class="admin-sidebar">
    <div class="sidebar-header">
        <h2>Admin Panel</h2>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="admin_dashboard.php">
                <span>📊</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="add_book.php">
                <span>➕</span>
                <span>Add New Books</span>
            </a>
        </li>
        <li>
            <a href="borrow.php">
                <span>⏳</span>
                <span>Borrowed Books</span>
            </a>
        </li>
        <li>
            <a href="return.php">
                <span>↩️</span>
                <span>Returned Books</span>
            </a>
        </li>
        <li>
            <a href="manage_book.php">
                <span>📚</span>
                <span>Manage Books</span>
            </a>
        </li>
        <li>
            <a href="manage_user.php">
                <span>👥</span>
                <span>Manage Users</span>
            </a>
        </li>
        <li>
            <a href="admin_recommendations.php">
                <span>💡</span>
                <span>Recommendations</span>
            </a>
        </li>
        <li>
        <a href="Main.html" onclick="return confirm('Are you sure you want to logout?');">
            <span>🚪</span>
            <span>Log Out</span>
        </a>
        </li>
    </ul>
</div>

<script>
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    let isOpen = false;

    toggleBtn.addEventListener('click', () => {
        isOpen = !isOpen;

        if (isOpen) {
            sidebar.style.width = '220px';
            // Fade in content after width transition completes
            setTimeout(() => {
                const elements = sidebar.querySelectorAll('div, a');
                elements.forEach(el => el.style.opacity = '1');
            }, 300);
            toggleBtn.style.right = '230px'; // Move button with sidebar
        } else {
            // Fade out content first
            const elements = sidebar.querySelectorAll('div, a');
            elements.forEach(el => el.style.opacity = '0');
            // Then collapse sidebar
            setTimeout(() => {
                sidebar.style.width = '0';
                toggleBtn.style.right = '10px'; // Return button to original position
            }, 200);
        }
    });
</script>