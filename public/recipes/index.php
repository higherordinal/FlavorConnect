<?php
require_once('../../private/core/initialize.php');
require_once(PRIVATE_PATH . '/classes/RecipeAttribute.class.php');

$page_title = 'Recipes';
$scripts = ['recipe-favorites']; // Load recipe-favorites.js

// Debug logging
error_log("Loading recipe gallery page");
error_log("Session state: " . print_r($_SESSION, true));
error_log("Scripts to load: " . print_r($scripts, true));

if($session->is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-gallery.css?v=1.0'); ?>">
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
$total_recipes = Recipe::count_all_filtered($search, $style_id, $diet_id, $type_id, $sort);
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
        
        <form class="search-form" action="<?php echo url_for('/recipes/index.php'); ?>" method="GET" role="search">
            <div class="search-bar">
                <input type="text" 
                       name="search" 
                       value="<?php echo h($search); ?>" 
                       placeholder="Search recipes..."
                       class="search-input"
                       aria-label="Search recipes">
                <button type="submit" class="search-button" aria-label="Submit search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                </button>
            </div>
        </form>
    </div>

    <div class="gallery-filters" role="group" aria-label="Recipe filters">
        <div class="filter-group">
            <label for="style-filter" class="filter-label" id="style-label">Style</label>
            <select id="style-filter" 
                    class="filter-select" 
                    name="style" 
                    aria-labelledby="style-label"
                    onchange="window.location.href='<?php echo url_for('/recipes/index.php?' . build_query_string(['style' => ''])); ?>' + this.value">
                <option value="">All Styles</option><?php foreach($styles as $style) { ?><option value="<?php echo h($style->id); ?>" <?php if($style_id === $style->id) echo 'selected'; ?>><?php echo h($style->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="diet-filter" class="filter-label" id="diet-label">Diet</label>
            <select id="diet-filter" 
                    class="filter-select" 
                    name="diet" 
                    aria-labelledby="diet-label"
                    onchange="window.location.href='<?php echo url_for('/recipes/index.php?' . build_query_string(['diet' => ''])); ?>' + this.value">
                <option value="">All Diets</option><?php foreach($diets as $diet) { ?><option value="<?php echo h($diet->id); ?>" <?php if($diet_id === $diet->id) echo 'selected'; ?>><?php echo h($diet->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="type-filter" class="filter-label" id="type-label">Type</label>
            <select id="type-filter" 
                    class="filter-select" 
                    name="type" 
                    aria-labelledby="type-label"
                    onchange="window.location.href='<?php echo url_for('/recipes/index.php?' . build_query_string(['type' => ''])); ?>' + this.value">
                <option value="">All Types</option><?php foreach($types as $type) { ?><option value="<?php echo h($type->id); ?>" <?php if($type_id === $type->id) echo 'selected'; ?>><?php echo h($type->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="sort-filter" class="filter-label" id="sort-label">Sort By</label>
            <select id="sort-filter" 
                    class="filter-select" 
                    name="sort" 
                    aria-labelledby="sort-label"
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
                <article class="recipe-card" role="article">
                    <?php if($session->is_logged_in()) { 
                        $is_favorited = $recipe->is_favorited_by($session->get_user_id());
                    ?>
                    <button class="favorite-btn <?php echo $is_favorited ? 'favorited' : ''; ?>"
                            data-recipe-id="<?php echo h($recipe->recipe_id); ?>"
                            aria-label="<?php echo $is_favorited ? 'Remove from' : 'Add to'; ?> favorites">
                        <i class="<?php echo $is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
                    </button>
                    <?php } ?>
                    <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id))); ?>" 
                       class="recipe-link"
                       aria-labelledby="recipe-title-<?php echo h($recipe->recipe_id); ?>">
                        <div class="recipe-image-container">
                            <img src="<?php echo $recipe->img_file_path ? url_for($recipe->img_file_path) : url_for('/assets/images/recipe-placeholder.jpg'); ?>" 
                                 alt="Photo of <?php echo h($recipe->title); ?>" 
                                 class="recipe-image">
                        </div>
                        
                        <div class="recipe-content">
                            <h2 class="recipe-title" id="recipe-title-<?php echo h($recipe->recipe_id); ?>"><?php echo h($recipe->title); ?></h2>
                            
                            <div class="recipe-meta">
                                <span class="rating" aria-label="Rating: <?php echo $recipe->get_average_rating(); ?> out of 5 stars">
                                    <?php 
                                        $rating = $recipe->get_average_rating();
                                        // Full stars
                                        for ($i = 1; $i <= floor($rating); $i++) {
                                            echo '★';
                                        }
                                        // Half star if needed
                                        if ($rating - floor($rating) >= 0.5) {
                                            echo '⯨';
                                        }
                                        // Empty stars
                                        $remaining = 5 - ceil($rating);
                                        echo str_repeat('☆', $remaining);
                                        echo ' <span class="review-count" aria-label="' . $recipe->rating_count() . ' reviews">(' . $recipe->rating_count() . ')</span>';
                                    ?>
                                </span>
                                <span class="time" aria-label="Total time: <?php echo $recipe->get_total_time_display(); ?>">
                                    <?php echo $recipe->get_total_time_display(); ?>
                                </span>
                            </div>

                            <div class="recipe-attributes" role="list">
                                <?php if($style) { ?>
                                    <span class="recipe-attribute"><?php echo h($style->name); ?></span>
                                <?php } ?>
                                <?php if($diet) { ?>
                                    <span class="recipe-attribute"><?php echo h($diet->name); ?></span>
                                <?php } ?>
                                <?php if($type) { ?>
                                    <span class="recipe-attribute"><?php echo h($type->name); ?></span>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="recipe-footer">
                            <div class="recipe-author">
                                <?php $user = User::find_by_id($recipe->user_id); ?>
                                <span class="author-name">By <?php echo h($user->username); ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php } ?>
        </div>

        <?php if($total_pages > 1) { ?>
            <nav class="pagination" role="navigation" aria-label="Recipe pages">
                <?php if($current_page > 1) { ?>
                    <a href="<?php echo url_for('/recipes/index.php?' . build_query_string(['page' => $current_page - 1])); ?>" 
                       class="page-link" 
                       aria-label="Go to previous page">
                        <i class="fas fa-chevron-left" aria-hidden="true"></i>
                        <span class="sr-only">Previous page</span>
                    </a>
                <?php } ?>

                <?php for($i = 1; $i <= $total_pages; $i++) { ?>
                    <?php if(
                        $i <= 2 || 
                        $i >= $total_pages - 1 || 
                        ($i >= $current_page - 1 && $i <= $current_page + 1)
                    ) { ?>
                        <a href="<?php echo url_for('/recipes/index.php?' . build_query_string(['page' => $i])); ?>" 
                           class="page-link <?php if($i === $current_page) echo 'active'; ?>"
                           aria-label="Go to page <?php echo $i; ?>"
                           <?php if($i === $current_page) echo 'aria-current="page"'; ?>>
                            <?php echo $i; ?>
                        </a>
                    <?php } elseif(
                        $i === 3 || 
                        $i === $total_pages - 2
                    ) { ?>
                        <span class="page-link" aria-hidden="true">...</span>
                    <?php } ?>
                <?php } ?>

                <?php if($current_page < $total_pages) { ?>
                    <a href="<?php echo url_for('/recipes/index.php?' . build_query_string(['page' => $current_page + 1])); ?>" 
                       class="page-link" 
                       aria-label="Go to next page">
                        <i class="fas fa-chevron-right" aria-hidden="true"></i>
                        <span class="sr-only">Next page</span>
                    </a>
                <?php } ?>
            </nav>
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
