<?php
require_once('../../../private/core/initialize.php');

require_admin();

$page_title = 'Admin: User Management';
$page_style = 'admin';
$component_styles = ['pagination', 'forms'];

// Get pagination parameters
$current_page = $_GET['page'] ?? 1;
$per_page = 10; // Number of users per page

// Get sort parameters
$sort_column = $_GET['sort'] ?? 'username';
$sort_order = $_GET['order'] ?? 'asc';

// Validate sort column to prevent SQL injection
$allowed_columns = ['username', 'first_name', 'last_name', 'email', 'is_active', 'user_level'];
if (!in_array($sort_column, $allowed_columns)) {
    $sort_column = 'username'; // Default if invalid
}

// Validate sort order
$sort_order = strtolower($sort_order) === 'desc' ? 'DESC' : 'ASC';

// Get total users count for pagination
$total_users = User::count_all();
if(!$session->is_super_admin()) {
    // Adjust count to exclude super admins
    $super_admin_count = User::count_by_level('s');
    $total_users -= $super_admin_count;
}

// Create pagination object
$pagination = new Pagination($current_page, $per_page, $total_users);

// Build the SQL query with pagination
$sql = "SELECT * FROM user_account";
if(!$session->is_super_admin()) {
    $sql .= " WHERE user_level != 's'";
}
$sql .= " ORDER BY {$sort_column} {$sort_order}";
$sql .= " LIMIT {$per_page} OFFSET {$pagination->offset()}";
$users = User::find_by_sql($sql);

// Function to generate sort URL
function sort_link($column, $current_sort, $current_order) {
    $new_order = ($current_sort === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';
    $current_page = $_GET['page'] ?? 1;
    return url_for('/admin/users/index.php') . '?sort=' . $column . '&order=' . $new_order . '&page=' . $current_page;
}

// Function to display sort indicator
function sort_indicator($column, $current_sort, $current_order) {
    if ($current_sort === $column) {
        return $current_order === 'ASC' ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>';
    }
    return ' <i class="fas fa-sort"></i>';
}

include(SHARED_PATH . '/member_header.php');
?>


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
                    <th>
                        <a href="<?php echo sort_link('username', $sort_column, $sort_order); ?>" class="sort-link">
                            Username<?php echo sort_indicator('username', $sort_column, $sort_order); ?>
                        </a>
                    </th>
                    <th>
                        <a href="<?php echo sort_link('first_name', $sort_column, $sort_order); ?>" class="sort-link">
                            First Name<?php echo sort_indicator('first_name', $sort_column, $sort_order); ?>
                        </a>
                    </th>
                    <th>
                        <a href="<?php echo sort_link('last_name', $sort_column, $sort_order); ?>" class="sort-link">
                            Last Name<?php echo sort_indicator('last_name', $sort_column, $sort_order); ?>
                        </a>
                    </th>
                    <th>
                        <a href="<?php echo sort_link('email', $sort_column, $sort_order); ?>" class="sort-link">
                            Email<?php echo sort_indicator('email', $sort_column, $sort_order); ?>
                        </a>
                    </th>
                    <th>
                        <a href="<?php echo sort_link('is_active', $sort_column, $sort_order); ?>" class="sort-link">
                            Status<?php echo sort_indicator('is_active', $sort_column, $sort_order); ?>
                        </a>
                    </th>
                    <th>
                        <a href="<?php echo sort_link('user_level', $sort_column, $sort_order); ?>" class="sort-link">
                            User Level<?php echo sort_indicator('user_level', $sort_column, $sort_order); ?>
                        </a>
                    </th>
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
    
    <!-- Pagination Controls -->
    <?php 
    // Generate pagination links
    // Use a base URL without the page parameter, and add sort and order as extra params
    $url_pattern = url_for('/admin/users/index.php') . '?page={page}';
    $extra_params = ['sort' => $sort_column, 'order' => strtolower($sort_order)];
    echo $pagination->page_links($url_pattern, $extra_params);
    
    // Display total records info
    echo '<div class="records-info">Showing ' . count($users) . ' of ' . $total_users . ' total users</div>';
    ?>
</div>

<div class="bottom-actions">
    <a href="<?php echo url_for('/admin/users/new.php'); ?>" class="action create">
        <i class="fas fa-plus"></i> Create New User
    </a>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
