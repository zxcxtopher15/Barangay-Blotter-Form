document.addEventListener('DOMContentLoaded', function() {
    // Select the necessary elements from the DOM
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const sidebarToggle = document.getElementById('sidebarToggle');

    // Ensure all elements exist before adding event listeners
    if (sidebar && mainContent && sidebarToggle) {
        
        // Function to handle the toggle action
        const toggleSidebar = () => {
            // Add or remove the 'collapsed' class from the sidebar
            sidebar.classList.toggle('collapsed');
            
            // Add or remove the 'collapsed' class from the main content area
            mainContent.classList.toggle('collapsed');

            // Save the current state (collapsed or not) to the browser's local storage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarState', isCollapsed ? 'collapsed' : 'expanded');
        };

        // Listen for clicks on the toggle button
        sidebarToggle.addEventListener('click', toggleSidebar);

        // Check if a state was previously saved in local storage
        if (localStorage.getItem('sidebarState') === 'collapsed') {
            // If it was collapsed, apply the 'collapsed' class to both elements
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
        }
        // Remove the initial load class to re-enable transitions after JS initializes
        document.documentElement.classList.remove('js-sidebar-initial-collapsed');
    }
});