<?php
require_once('../private/core/initialize.php');
require_login();

$page_title = 'Debug Recipe';
include(SHARED_PATH . '/member_header.php');

// Get recipe ID from URL
$id = $_GET['id'] ?? '';

if(empty($id)) {
    $session->message('No recipe ID provided.', 'error');
    redirect_to(url_for('/recipes/index.php'));
}

// Find recipe
$recipe = Recipe::find_by_id($id);

if(!$recipe) {
    $session->message('Recipe not found.', 'error');
    redirect_to(url_for('/recipes/index.php'));
}

// Check if user has permission to view this debug info
if($recipe->user_id != $session->get_user_id() && !$session->is_admin()) {
    $session->message('You do not have permission to debug this recipe.', 'error');
    redirect_to(url_for('/recipes/show.php?id=' . $id));
}

// Get recipe ingredients and steps
$ingredients = RecipeIngredient::find_by_recipe_id($recipe->recipe_id);
$steps = RecipeStep::find_by_recipe_id($recipe->recipe_id);
?>

<div class="container mt-4">
    <h1>Recipe Debug Information</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Recipe Details</h2>
        </div>
        <div class="card-body">
            <p><strong>Recipe ID:</strong> <?php echo h($recipe->recipe_id); ?></p>
            <p><strong>Title:</strong> <?php echo h($recipe->title); ?></p>
            <p><strong>User ID:</strong> <?php echo h($recipe->user_id); ?></p>
            <p><strong>Created At:</strong> <?php echo h($recipe->created_at); ?></p>
            <p><strong>Updated At:</strong> <?php echo h($recipe->updated_at); ?></p>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Ingredients (<?php echo count($ingredients); ?>)</h2>
        </div>
        <div class="card-body">
            <?php if(!empty($ingredients)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ingredient ID</th>
                            <th>Name</th>
                            <th>Quantity</th>
                            <th>Measurement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($ingredients as $ingredient): ?>
                            <tr>
                                <td><?php echo h($ingredient->recipe_ingredient_id); ?></td>
                                <td><?php echo h($ingredient->ingredient_id); ?></td>
                                <td><?php echo h($ingredient->name); ?></td>
                                <td><?php echo h($ingredient->quantity); ?></td>
                                <td>
                                    <?php 
                                    if($ingredient->measurement_id) {
                                        $measurement = Measurement::find_by_id($ingredient->measurement_id);
                                        echo h($measurement->name ?? 'Unknown');
                                    } else {
                                        echo 'None';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-danger">No ingredients found for this recipe.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Steps (<?php echo count($steps); ?>)</h2>
        </div>
        <div class="card-body">
            <?php if(!empty($steps)): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Step Number</th>
                            <th>Instruction</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($steps as $step): ?>
                            <tr>
                                <td><?php echo h($step->step_id); ?></td>
                                <td><?php echo h($step->step_number); ?></td>
                                <td><?php echo h($step->instruction); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-danger">No steps found for this recipe.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>PHP Error Log (Last 20 Lines)</h2>
        </div>
        <div class="card-body">
            <?php
            $log_file = ini_get('error_log');
            if(file_exists($log_file) && is_readable($log_file)) {
                $log_content = shell_exec('tail -n 20 ' . escapeshellarg($log_file));
                echo '<pre>' . h($log_content) . '</pre>';
            } else {
                echo '<p class="text-danger">Error log not found or not readable.</p>';
                echo '<p>Configured log path: ' . h($log_file) . '</p>';
            }
            ?>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Database Tables</h2>
        </div>
        <div class="card-body">
            <h3>recipe_ingredient Table Structure</h3>
            <?php
            $sql = "DESCRIBE recipe_ingredient";
            $result = Recipe::$database->query($sql);
            if($result) {
                echo '<table class="table table-sm">';
                echo '<thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>';
                echo '<tbody>';
                while($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . h($row['Field']) . '</td>';
                    echo '<td>' . h($row['Type']) . '</td>';
                    echo '<td>' . h($row['Null']) . '</td>';
                    echo '<td>' . h($row['Key']) . '</td>';
                    echo '<td>' . h($row['Default']) . '</td>';
                    echo '<td>' . h($row['Extra']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="text-danger">Error querying table structure.</p>';
            }
            ?>
            
            <h3>ingredient Table Structure</h3>
            <?php
            $sql = "DESCRIBE ingredient";
            $result = Recipe::$database->query($sql);
            if($result) {
                echo '<table class="table table-sm">';
                echo '<thead><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr></thead>';
                echo '<tbody>';
                while($row = $result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . h($row['Field']) . '</td>';
                    echo '<td>' . h($row['Type']) . '</td>';
                    echo '<td>' . h($row['Null']) . '</td>';
                    echo '<td>' . h($row['Key']) . '</td>';
                    echo '<td>' . h($row['Default']) . '</td>';
                    echo '<td>' . h($row['Extra']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p class="text-danger">Error querying table structure.</p>';
            }
            ?>
        </div>
    </div>
    
    <div class="mt-4 mb-5">
        <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($id))); ?>" class="btn btn-primary">Back to Recipe</a>
        <a href="<?php echo url_for('/recipes/edit.php?id=' . h(u($id))); ?>" class="btn btn-secondary">Edit Recipe</a>
    </div>
</div>

<?php include(SHARED_PATH . '/public_footer.php'); ?>
