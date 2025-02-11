<?php
require_once('../core/initialize.php');
require_admin();

$page_title = 'admin';
$page_style = 'admin';
include(SHARED_PATH . '/member_header.php');
?>

<div class="admin-dashboard">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
    </div>

    <div class="admin-modules">
        <div class="admin-module">
            <h2>User Management</h2>
            <p>Manage user accounts, roles, and permissions.</p>
            <div class="actions">
                <a href="<?php echo private_url_for('/admin/users/index.php'); ?>" class="action">
                    <i class="fas fa-users"></i>
                    Manage Users
                </a>
            </div>
        </div>

        <div class="admin-module">
            <h2>Category Management</h2>
            <p>Manage recipe categories and organization.</p>
            <div class="actions">
                <a href="<?php echo private_url_for('/admin/categories/index.php'); ?>" class="action">
                    <i class="fas fa-tags"></i>
                    Manage Categories
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    max-width: var(--container-width);
    margin: var(--spacing-xl) auto;
    padding: 0 var(--spacing-md);
}

.admin-modules {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: var(--spacing-lg);
    margin-top: var(--spacing-lg);
}

.admin-module {
    background: var(--surface-color);
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    box-shadow: var(--shadow-sm);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.admin-module:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.admin-module h2 {
    color: var(--text-color);
    margin-bottom: var(--spacing-sm);
    font-size: 1.5rem;
}

.admin-module p {
    color: var(--text-secondary-color);
    margin-bottom: var(--spacing-md);
}

.admin-module .actions {
    margin-top: var(--spacing-md);
}

.admin-module .action {
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-sm);
    background: var(--primary-color);
    color: var(--on-primary-color);
    padding: var(--spacing-sm) var(--spacing-md);
    border-radius: var(--border-radius-sm);
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.2s ease;
}

.admin-module .action:hover {
    background: var(--primary-dark-color);
}

.admin-module .action i {
    font-size: 1.25rem;
}

@media (max-width: 768px) {
    .admin-modules {
        grid-template-columns: 1fr;
    }
}
</style>

<?php include(SHARED_PATH . '/footer.php'); ?>
