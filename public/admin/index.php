<?php
require_once('../../private/core/initialize.php');

require_admin();

$page_title = 'Admin Dashboard';
$page_style = 'admin';

// Scripts
$utility_scripts = ['common', 'back-link'];
$component_scripts = ['recipe-favorite'];
$page_scripts = ['admin'];

// Determine the back link based on the ref_page parameter
$back_link = '/index.php';
$back_text = 'Back to Home';

if (isset($_GET['ref_page']) && !empty($_GET['ref_page'])) {
    $ref_page = $_GET['ref_page'];
    
    // Make sure the ref_page is a valid internal URL
    if (strpos($ref_page, '/') === 0) {
        // It's a valid internal URL, use it as the back link
        $back_link = $ref_page;
        
        // Set appropriate back text based on the back link
        if (strpos($back_link, '/recipes/index.php') !== false) {
            $back_text = 'Back to Recipes';
        } elseif (strpos($back_link, '/users/favorites.php') !== false) {
            $back_text = 'Back to Favorites';
        } elseif (strpos($back_link, '/users/profile.php') !== false) {
            $back_text = 'Back to Profile';
        } elseif (strpos($back_link, '/index.php') !== false) {
            $back_text = 'Back to Home';
        } else {
            // Generic back text for other pages
            $back_text = 'Back';
        }
    }
}

include(SHARED_PATH . '/member_header.php');
?>

<div class="admin-dashboard">
    <?php 
    echo unified_navigation(
        $back_link,
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['label' => 'Admin Dashboard']
        ],
        $back_text
    ); 
    ?>
    
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="admin-modules">
        <div class="admin-module">
            <h2>User Management</h2>
            <p>Manage user accounts, roles, and permissions.</p>
            <div class="actions">
                <a href="<?php echo url_for('/admin/users/index.php'); ?>" class="action">
                    <i class="fas fa-users"></i>
                    Manage Users
                </a>
            </div>
        </div>

        <div class="admin-module">
            <h2>Category Management</h2>
            <p>Manage recipe categories and organization.</p>
            <div class="actions">
                <a href="<?php echo url_for('/admin/categories/index.php'); ?>" class="action">
                    <i class="fas fa-tags"></i>
                    Manage Categories
                </a>
            </div>
        </div>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
