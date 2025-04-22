<?php
require_once('../../private/core/initialize.php');

require_admin();

$page_title = 'Admin Dashboard';
$page_style = 'admin';

// Scripts
// Note: 'common' and 'back-link' are already loaded in member_header.php
$utility_scripts = [];
$component_scripts = ['recipe-favorite'];
$page_scripts = ['admin'];

// Use the standardized get_back_link function for consistent back link handling
$back_link_data = get_back_link('/index.php');
$back_link = $back_link_data['url'];
$back_text = $back_link_data['text'];

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
