<!-- Toggle Button (Right Side) -->
<button id="sidebarToggle" class="sidebar-toggle">
    ☰
</button>

<!-- Sidebar (Right Side) -->
<div id="adminSidebar" class="admin-sidebar">
    <div class="sidebar-header">
        <h2>User Panel</h2>
    </div>
    <ul class="sidebar-menu">
        <li>
            <a href="user_dashboard.php">
                <span>🏠</span>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="borrowed_books.php">
                <span>📖</span>
                <span>View Borrowed Books</span>
            </a>
        </li>
        <li>
            <a href="returned_books.php">
                <span>✅</span>
                <span>View Returned Books</span>
            </a>
        </li>
        <li>
            <a href="category.php">
                <span>🗂️</span>
                <span>Category</span>
            </a>
        </li>
        <li>
            <a href="user_settings.php">
                <span>⚙️</span>
                <span>Settings</span>
            </a>
        </li>
        <li>
            <a href="user_recommendations.php">
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