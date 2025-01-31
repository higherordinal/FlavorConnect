<?php
require_once('../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/classes/RecipeAttribute.class.php');

$page_title = 'Recipes';
$scripts = ['recipe-favorites']; // Load recipe-favorites.js

// Debug logging
error_log("Loading recipe gallery page");
error_log("Session state: " . print_r($_SESSION, true));
error_log("Scripts to load: " . print_r($scripts, true));

include(SHARED_PATH . '/public_header.php');
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-gallery.css'); ?>">
<?php
// Get filter values
$search = $_GET['search'] ?? '';
$style_id = !empty($_GET['style']) ? (int)$_GET['style'] : null;
$diet_id = !empty($_GET['diet']) ? (int)$_GET['diet'] : null;
$type_id = !empty($_GET['type']) ? (int)$_GET['type'] : null;
$sort = $_GET['sort'] ?? 'newest';

// Get current page
$current_page = $_GET['page'] ?? 1;
$current_page = max(1, (int)$current_page);

// Set recipes per page
$per_page = 12;

// Get filter options
$styles = RecipeAttribute::find_by_type('style');
$diets = RecipeAttribute::find_by_type('diet');
$types = RecipeAttribute::find_by_type('type');

// Calculate offset
$offset = ($current_page - 1) * $per_page;

// Get total recipes count for pagination
$total_recipes = Recipe::count_all_filtered($search, $style_id, $diet_id, $type_id);
$total_pages = ceil($total_recipes / $per_page);

// Get recipes for current page
$recipes = Recipe::find_all_filtered($search, $style_id, $diet_id, $type_id, $sort, $per_page, $offset);

// Ensure current page is not greater than total pages
if ($current_page > $total_pages) {
    redirect_to('/recipes/index.php');
}
?>

<div class="recipe-gallery">
    <div class="gallery-header">
        <h1 class="gallery-title">Recipes</h1>
        
        <form class="search-form" action="<?php echo url_for('/recipes/index.php'); ?>" method="GET">
            <div class="search-bar">
                <input type="text" 
                       name="search" 
                       value="<?php echo h($search); ?>" 
                       placeholder="Search recipes..."
                       class="search-input">
                <button type="submit" class="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="gallery-filters">
        <div class="filter-group">
            <label for="style-filter" class="filter-label">Style</label>
            <select id="style-filter" class="filter-select" name="style" 
                    onchange="window.location.href='<?php echo url_for('/recipes/index.php?' . build_query_string(['style' => ''])); ?>' + this.value">
                <option value="">All Styles</option><?php foreach($styles as $style) { ?><option value="<?php echo h($style->id); ?>" <?php if($style_id === $style->id) echo 'selected'; ?>><?php echo h($style->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="diet-filter" class="filter-label">Diet</label>
            <select id="diet-filter" class="filter-select" name="diet" 
                    onchange="window.location.href='<?php echo url_for('/recipes/index.php?' . build_query_string(['diet' => ''])); ?>' + this.value">
                <option value="">All Diets</option><?php foreach($diets as $diet) { ?><option value="<?php echo h($diet->id); ?>" <?php if($diet_id === $diet->id) echo 'selected'; ?>><?php echo h($diet->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="type-filter" class="filter-label">Type</label>
            <select id="type-filter" class="filter-select" name="type" 
                    onchange="window.location.href='<?php echo url_for('/recipes/index.php?' . build_query_string(['type' => ''])); ?>' + this.value">
                <option value="">All Types</option><?php foreach($types as $type) { ?><option value="<?php echo h($type->id); ?>" <?php if($type_id === $type->id) echo 'selected'; ?>><?php echo h($type->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="sort-filter" class="filter-label">Sort By</label>
            <select id="sort-filter" class="filter-select" name="sort" 
                    onchange="window.location.href='<?php echo url_for('/recipes/index.php?' . build_query_string(['sort' => ''])); ?>' + this.value">
                <option value="newest" <?php if($sort === 'newest') echo 'selected'; ?>>Newest First</option><option value="oldest" <?php if($sort === 'oldest') echo 'selected'; ?>>Oldest First</option><option value="name_asc" <?php if($sort === 'name_asc') echo 'selected'; ?>>Name A-Z</option><option value="name_desc" <?php if($sort === 'name_desc') echo 'selected'; ?>>Name Z-A</option>
            </select>
        </div>

        <?php if($search || $style_id || $diet_id || $type_id) { ?>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="clear-filters">
                <i class="fas fa-times"></i> Clear Filters
            </a>
        <?php } ?>
    </div>

    <div class="filter-summary">
        <p class="results-count"><?php echo $total_recipes; ?> recipes found</p>
        <?php if($search || $style_id || $diet_id || $type_id) { ?>
            <div class="active-filters">
                <?php if($search) { ?>
                    <span class="filter-tag">
                        Search: <?php echo h($search); ?>
                    </span>
                <?php } ?>
                <?php if($style_id && ($style = RecipeAttribute::find_by_id($style_id))) { ?>
                    <span class="filter-tag">
                        Style: <?php echo h($style->name); ?>
                    </span>
                <?php } ?>
                <?php if($diet_id && ($diet = RecipeAttribute::find_by_id($diet_id))) { ?>
                    <span class="filter-tag">
                        Diet: <?php echo h($diet->name); ?>
                    </span>
                <?php } ?>
                <?php if($type_id && ($type = RecipeAttribute::find_by_id($type_id))) { ?>
                    <span class="filter-tag">
                        Type: <?php echo h($type->name); ?>
                    </span>
                <?php } ?>
            </div>
        <?php } ?>
    </div>

    <?php if(empty($recipes)) { ?>
        <div class="no-results">
            <p>No recipes found matching your criteria.</p>
            <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn btn-primary">Clear Filters</a>
        </div>
    <?php } else { ?>
        <div class="recipe-grid">
            <?php foreach($recipes as $recipe) { 
                $style = $recipe->style();
                $diet = $recipe->diet();
                $type = $recipe->type();
            ?>
                <article class="recipe-card">
                    <?php if($session->is_logged_in()) { 
                        $is_favorited = UserFavorite::is_favorite($session->get_user_id(), $recipe->recipe_id);
                    ?>
                        <button type="button" class="favorite-btn <?php echo $is_favorited ? 'active' : ''; ?>" 
                                data-recipe-id="<?php echo h($recipe->recipe_id); ?>"
                                data-is-favorited="<?php echo $is_favorited ? 'true' : 'false'; ?>">
                            <i class="fa-heart <?php echo $is_favorited ? 'fas' : 'far'; ?>"></i>
                        </button>
                    <?php } ?>
                    <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id))); ?>" class="recipe-link">
                    <img src="<?php echo url_for('/assets/images/recipe-placeholder.jpg'); ?>" alt="<?php echo h($recipe->title); ?>" class="recipe-image">
                    
                    <div class="recipe-content">
                        <h2 class="recipe-title"><?php echo h($recipe->title); ?></h2>
                        
                        <div class="recipe-meta">
                            <span class="rating">
                                <?php 
                                    $rating = $recipe->get_average_rating();
                                    echo str_repeat('★', round($rating));
                                    echo str_repeat('☆', 5 - round($rating));
                                    echo " (" . $recipe->rating_count() . ")";
                                ?>
                            </span>
                            <span class="time">
                                <?php echo $recipe->get_total_time_display(); ?>
                            </span>
                        </div>

                        <div class="recipe-tags">
                            <?php if($style) { ?>
                                <span class="recipe-tag"><?php echo h($style->name); ?></span>
                            <?php } ?>
                            <?php if($diet) { ?>
                                <span class="recipe-tag"><?php echo h($diet->name); ?></span>
                            <?php } ?>
                            <?php if($type) { ?>
                                <span class="recipe-tag"><?php echo h($type->name); ?></span>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="recipe-footer">
                        <div class="recipe-author">
                            <?php $user = User::find_by_id($recipe->user_id); ?>
                            <span class="author-name">By <?php echo h($user->username); ?></span>
                        </div>

                        <div class="recipe-rating">
                            <i class="fas fa-star"></i>
                            <span><?php echo number_format($recipe->get_average_rating(), 1); ?></span>
                        </div>
                    </div>
                    </a>
                </article>
            <?php } ?>
        </div>

        <?php if($total_pages > 1) { ?>
            <div class="pagination">
                <?php if($current_page > 1) { ?>
                    <a href="<?php echo url_for('/recipes/index.php?' . build_query_string(['page' => $current_page - 1])); ?>" 
                       class="page-link" aria-label="Previous page">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php } ?>

                <?php for($i = 1; $i <= $total_pages; $i++) { ?>
                    <?php if(
                        $i <= 2 || 
                        $i >= $total_pages - 1 || 
                        ($i >= $current_page - 1 && $i <= $current_page + 1)
                    ) { ?>
                        <a href="<?php echo url_for('/recipes/index.php?' . build_query_string(['page' => $i])); ?>" 
                           class="page-link <?php if($i === $current_page) echo 'active'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php } elseif(
                        $i === 3 || 
                        $i === $total_pages - 2
                    ) { ?>
                        <span class="page-link">...</span>
                    <?php } ?>
                <?php } ?>

                <?php if($current_page < $total_pages) { ?>
                    <a href="<?php echo url_for('/recipes/index.php?' . build_query_string(['page' => $current_page + 1])); ?>" 
                       class="page-link" aria-label="Next page">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php } ?>
            </div>
        <?php } ?>
    <?php } ?>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>


<?php
// Helper function to maintain query parameters
function build_query_string($params_to_update=[]) {
    $params = $_GET;
    foreach($params_to_update as $key => $value) {
        $params[$key] = $value;
    }
    return http_build_query($params);
}
?>
