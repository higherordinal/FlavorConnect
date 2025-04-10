<?php
require_once('../../private/core/initialize.php');
require_login();

$user_id = $session->get_user_id();
$user = User::find_by_id($user_id);

if(!$user) {
    $session->message('Error: User not found.');
    redirect_to(url_for('/index.php'));
}

// Get current page
$current_page = $_GET['page'] ?? 1;
$current_page = max(1, (int)$current_page);

// Set recipes per page
$per_page = 6;

// Calculate offset
$offset = ($current_page - 1) * $per_page;

// Get total count of user's recipes
$database = Recipe::get_database();
$count_sql = "SELECT COUNT(*) as count FROM recipe WHERE user_id = ?";
$count_stmt = $database->prepare($count_sql);
$count_stmt->bind_param("i", $user_id);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_recipes = (int)$count_row['count'];

// Create pagination object
$pagination = new Pagination($current_page, $per_page, $total_recipes);

// Get paginated recipes
$sql = "SELECT * FROM recipe WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $database->prepare($sql);
$stmt->bind_param("iii", $user_id, $per_page, $offset);
$stmt->execute();
$result = $stmt->get_result();
$recipes = Recipe::create_objects_from_result($result);

$page_title = 'User Profile';
$page_style = 'user-profile';
$component_styles = ['pagination']; // Add pagination styles
include(SHARED_PATH . '/member_header.php');
?>

<div class="profile-container">
    <?php 
    echo unified_navigation(
        '/index.php',
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['label' => 'Profile']
        ],
        'Back to Home'
    ); 
    ?>
    
    <div class="profile-header">
        <h1><?php echo h($user->username); ?></h1>
        <div class="profile-stats">
            <div class="stat">
                <i class="fas fa-utensils" aria-hidden="true"></i>
                <span><?php echo $total_recipes; ?> Recipes Created</span>
            </div>
        </div>
    </div>

    <div class="profile-content">
        <section class="my-recipes">
            <div class="section-header">
                <h2>My Recipes</h2>
                <a href="<?php echo url_for('/recipes/new.php?ref=profile'); ?>" class="btn btn-primary" aria-label="Create a new recipe">
                    <i class="fas fa-plus" aria-hidden="true"></i> Create New Recipe
                </a>
            </div>

            <?php if(empty($recipes)) { ?>
                <div class="empty-state">
                    <i class="fas fa-book-open" aria-hidden="true"></i>
                    <p>You haven't created any recipes yet.</p>
                    <a href="<?php echo url_for('/recipes/new.php?ref=profile'); ?>" class="btn btn-primary" aria-label="Create your first recipe">Create Your First Recipe</a>
                </div>
            <?php } else { ?>
                <div class="recipe-grid">
                    <?php foreach($recipes as $recipe) { ?>
                        <div class="recipe-card">
                            <div class="recipe-image">
                                <?php if($recipe->get_image_path('thumb')) { ?>
                                    <img src="<?php echo url_for($recipe->get_image_path('thumb')); ?>" 
                                         alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
                                <?php } else { ?>
                                    <img src="<?php echo url_for('/assets/images/recipe-placeholder.png'); ?>" 
                                         alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
                                <?php } ?>
                            </div>
                            <div class="recipe-content">
                                <h3><?php echo h($recipe->title); ?></h3>
                                <div class="recipe-meta">
                                    <span>
                                        <i class="fas fa-clock" aria-hidden="true"></i>
                                        <span><?php echo h(TimeUtility::format_time($recipe->prep_time + $recipe->cook_time)); ?></span>
                                    </span>
                                    <span>
                                        <i class="fas fa-utensils" aria-hidden="true"></i>
                                        <span><?php echo h($recipe->style() ? $recipe->style()->name : 'Any Style'); ?></span>
                                    </span>
                                </div>
                                <div class="recipe-actions">
                                    <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id))); ?>" 
                                       class="btn btn-secondary" aria-label="View recipe: <?php echo h($recipe->title); ?>">
                                        <i class="fas fa-eye" aria-hidden="true"></i> View
                                    </a>
                                    <a href="<?php echo url_for('/recipes/edit.php?id=' . h(u($recipe->recipe_id)) . '&ref=profile'); ?>" 
                                       class="btn btn-primary" aria-label="Edit recipe: <?php echo h($recipe->title); ?>">
                                        <i class="fas fa-edit" aria-hidden="true"></i> Edit
                                    </a>
                                    <a href="<?php echo url_for('/recipes/delete.php?id=' . h(u($recipe->recipe_id)) . '&ref=profile'); ?>" 
                                       class="btn btn-danger"
                                       aria-label="Delete recipe: <?php echo h($recipe->title); ?>"
                                       onclick="return confirm('Are you sure you want to delete this recipe?');">
                                        <i class="fas fa-trash" aria-hidden="true"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                
                <?php if($pagination->total_pages() > 1) { ?>
                    <!-- Pagination Controls -->
                    <?php 
                    // Check if we can use the route_links method with named routes
                    if (function_exists('route')) {
                        // Use route_links with the 'users.profile' named route
                        try {
                            echo $pagination->route_links('users.profile', [], 'page');
                        } catch (Exception $e) {
                            // Fallback to traditional method if route_links fails
                            $url_pattern = url_for('/users/profile.php') . '?page={page}';
                            echo $pagination->page_links($url_pattern);
                        }
                    } else {
                        // Fallback to traditional method
                        $url_pattern = url_for('/users/profile.php') . '?page={page}';
                        echo $pagination->page_links($url_pattern);
                    }
                    
                    // Display total records info
                    echo '<div class="records-info">Showing ' . count($recipes) . ' of ' . $total_recipes . ' total recipes</div>';
                    ?>
                <?php } ?>
            <?php } ?>
        </section>
    </div>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
