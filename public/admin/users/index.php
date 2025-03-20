<?php
require_once('../../../private/core/initialize.php');

require_login();
require_admin();

$page_title = 'Admin: User Management';
$page_style = 'admin';

// Get all users, but regular admins can't see super admins
$sql = "SELECT * FROM user_account";
if(!$session->is_super_admin()) {
    $sql .= " WHERE user_level != 's'";
}
$sql .= " ORDER BY username ASC";
$users = User::find_by_sql($sql);

include(SHARED_PATH . '/member_header.php');
?>

<main class="main-content">
    <div class="admin-content">
        <?php 
        echo unified_navigation(
            '/admin/index.php',
            [
                ['url' => '/index.php', 'label' => 'Home'],
                ['url' => '/admin/index.php', 'label' => 'Admin'],
                ['label' => 'User Management']
            ],
            'Back to Admin Dashboard'
        ); 
        ?>

        <div class="admin-header">
            <h1>User Management</h1>
        </div>

        <?php echo display_session_message(); ?>

        <div class="table-responsive">
            <table class="list">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>User Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user) { ?>
                        <tr>
                            <td data-label="Username"><?php echo h($user->username); ?></td>
                            <td data-label="First Name"><?php echo h($user->first_name); ?></td>
                            <td data-label="Last Name"><?php echo h($user->last_name); ?></td>
                            <td data-label="Email"><?php echo h($user->email); ?></td>
                            <td data-label="Status">
                                <span class="status-badge <?php echo $user->is_active ? 'active' : 'inactive'; ?>">
                                    <?php echo $user->is_active ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td data-label="User Level">
                                <?php
                                $level = '';
                                switch($user->user_level) {
                                    case 's':
                                        $level = 'Super Admin';
                                        break;
                                    case 'a':
                                        $level = 'Admin';
                                        break;
                                    default:
                                        $level = 'User';
                                }
                                echo h($level);
                                ?>
                            </td>
                            <td data-label="Actions" class="actions">
                                <a href="<?php echo url_for('/admin/users/edit.php?user_id=' . h(u($user->user_id))); ?>" 
                                   class="action" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if($user->user_level !== 's') { ?>
                                    <a href="<?php echo url_for('/admin/users/delete.php?user_id=' . h(u($user->user_id))); ?>" 
                                       class="action delete" 
                                       onclick="return confirm('Are you sure you want to delete this user?');"
                                       title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="bottom-actions">
    <a href="<?php echo url_for('/admin/users/new.php'); ?>" class="action create">
        <i class="fas fa-plus"></i> Create New User
    </a>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
