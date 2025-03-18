<?php
require_once('../private/core/initialize.php');
require_login();

$page_title = 'Database Structure';
include(SHARED_PATH . '/member_header.php');

// Check if user is admin
if(!$session->is_admin()) {
    $session->message('You do not have permission to view this page.', 'error');
    redirect_to(url_for('/index.php'));
}

// Function to get table structure
function get_table_structure($table_name) {
    $sql = "DESCRIBE " . $table_name;
    $result = Recipe::$database->query($sql);
    
    if(!$result) {
        return "Error getting table structure: " . Recipe::$database->error;
    }
    
    $structure = [];
    while($row = $result->fetch_assoc()) {
        $structure[] = $row;
    }
    
    return $structure;
}

// Tables to check
$tables = ['recipe', 'ingredient', 'recipe_ingredient', 'measurement'];
?>

<div class="container mt-4">
    <h1>Database Structure</h1>
    
    <?php foreach($tables as $table): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h2><?php echo h($table); ?> Table</h2>
            </div>
            <div class="card-body">
                <?php
                $structure = get_table_structure($table);
                if(is_array($structure)):
                ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Null</th>
                                <th>Key</th>
                                <th>Default</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($structure as $column): ?>
                                <tr>
                                    <td><?php echo h($column['Field']); ?></td>
                                    <td><?php echo h($column['Type']); ?></td>
                                    <td><?php echo h($column['Null']); ?></td>
                                    <td><?php echo h($column['Key']); ?></td>
                                    <td><?php echo h($column['Default'] ?? 'NULL'); ?></td>
                                    <td><?php echo h($column['Extra']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-danger"><?php echo h($structure); ?></p>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div class="mt-4 mb-5">
        <a href="<?php echo url_for('/index.php'); ?>" class="btn btn-primary">Back to Home</a>
    </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
