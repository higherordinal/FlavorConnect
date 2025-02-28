<?php
require_once('../../private/core/initialize.php');

require_login();
require_admin();

$page_title = 'Admin Dashboard';
$page_style = 'admin';
include(SHARED_PATH . '/member_header.php');
?>

<main class="main-content">
    <div class="admin-dashboard">
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
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
