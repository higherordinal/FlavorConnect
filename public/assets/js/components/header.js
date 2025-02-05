// Toggle user dropdown menu
document.addEventListener('DOMContentLoaded', function() {
    const userDropdown = document.querySelector('.user-dropdown');
    const userMenu = document.querySelector('.user-menu');

    if (userDropdown && userMenu) {
        userDropdown.addEventListener('click', function(e) {
            e.preventDefault();
            userMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                userMenu.classList.remove('show');
            }
        });
    }
});
