<?php
require_once('../../private/core/initialize.php');

$page_title = 'Recipes';
$page_style = 'recipe-gallery';

if($session->is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}
?>
<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-gallery.css?v=1.0'); ?>">

<div class="container">
    <?php 
    echo unified_navigation(
        '/index.php',
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['label' => 'Recipes']
        ],
        'Back to Home'
    ); 
    ?>
</div>

<?php 
// Get filter values
$search = $_GET['search'] ?? '';
$style_id = !empty($_GET['style']) ? (int)$_GET['style'] : null;
$diet_id = !empty($_GET['diet']) ? (int)$_GET['diet'] : null;
$type_id = !empty($_GET['type']) ? (int)$_GET['type'] : null;
$sort = $_GET['sort'] ?? 'newest';

// Validate filter IDs against database to ensure they exist
if (!empty($diet_id)) {
    $diet = RecipeAttribute::find_one($diet_id, 'diet');
    if (!$diet) {
        // If diet doesn't exist, keep the filter but it will return 0 results
        // This is intentional to avoid showing all recipes when a non-existent diet is selected
    }
}

if (!empty($style_id)) {
    $style = RecipeAttribute::find_one($style_id, 'style');
    if (!$style) {
        // If style doesn't exist, keep the filter but it will return 0 results
        // This is intentional to avoid showing all recipes when a non-existent style is selected
    }
}

if (!empty($type_id)) {
    $type = RecipeAttribute::find_one($type_id, 'type');
    if (!$type) {
        // If type doesn't exist, keep the filter but it will return 0 results
        // This is intentional to avoid showing all recipes when a non-existent type is selected
    }
}

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

// Create filter configuration
$filter_config = [
    'search' => $search,
    'style_id' => $style_id,
    'diet_id' => $diet_id,
    'type_id' => $type_id,
    'sort' => $sort,
    'limit' => $per_page,
    'offset' => $offset,
    'include_favorites' => $session->is_logged_in(),
    'user_id' => $session->is_logged_in() ? $session->get_user_id() : null
];

// Create and apply the filter
$recipe_filter = new RecipeFilter($filter_config);

// Get total recipes count for pagination
$total_recipes = $recipe_filter->count();
$total_pages = max(1, ceil($total_recipes / $per_page));

// Get recipes for current page
$recipes = $recipe_filter->apply();

// Ensure current page is not greater than total pages
if ($current_page > $total_pages) {
    redirect_to('/recipes/index.php');
}

// Prepare data for JavaScript
$recipesData = array_map(function($recipe) use ($session) {
    $style = $recipe->style();
    $diet = $recipe->diet();
    $type = $recipe->type();
    $user = User::find_by_id($recipe->user_id);
    $rating = $recipe->get_average_rating();
    
    return [
        'recipe_id' => $recipe->recipe_id,
        'user_id' => $session->is_logged_in() ? $session->get_user_id() : null,
        'username' => $user ? $user->username : 'Unknown',
        'title' => $recipe->title,
        'description' => $recipe->description,
        'style_id' => $recipe->style_id,
        'style' => $style ? $style->name : null,
        'diet_id' => $recipe->diet_id,
        'diet' => $diet ? $diet->name : null,
        'type_id' => $recipe->type_id,
        'type' => $type ? $type->name : null,
        'prep_time' => $recipe->prep_time,
        'cook_time' => $recipe->cook_time,
        'img_file_path' => $recipe->img_file_path,
        'created_at' => $recipe->created_at,
        'rating' => $rating['average'] ?? null,
        'rating_count' => $rating['count'] ?? 0,
        'is_favorited' => $recipe->is_favorited ?? false
    ];
}, $recipes);

$userData = [
    'isLoggedIn' => $session->is_logged_in(),
    'userId' => $session->is_logged_in() ? $session->get_user_id() : null,
    'apiBaseUrl' => 'http://localhost:3000'
];
?>

<script>
    // Initialize data for JavaScript
    window.initialUserData = <?php echo json_encode($userData); ?>;
    window.initialRecipesData = <?php echo json_encode($recipesData); ?>;
</script>

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
                    data-filter-type="style">
                <option value="">All Styles</option><?php foreach($styles as $style) { ?><option value="<?php echo h($style->id); ?>" <?php if($style_id === $style->id) echo 'selected'; ?>><?php echo h($style->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="diet-filter" class="filter-label" id="diet-label">Diet</label>
            <select id="diet-filter" 
                    class="filter-select" 
                    name="diet" 
                    aria-labelledby="diet-label"
                    data-filter-type="diet">
                <option value="">All Diets</option><?php foreach($diets as $diet) { ?><option value="<?php echo h($diet->id); ?>" <?php if($diet_id === $diet->id) echo 'selected'; ?>><?php echo h($diet->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="type-filter" class="filter-label" id="type-label">Type</label>
            <select id="type-filter" 
                    class="filter-select" 
                    name="type" 
                    aria-labelledby="type-label"
                    data-filter-type="type">
                <option value="">All Types</option><?php foreach($types as $type) { ?><option value="<?php echo h($type->id); ?>" <?php if($type_id === $type->id) echo 'selected'; ?>><?php echo h($type->name); ?></option><?php } ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="sort-filter" class="filter-label" id="sort-label">Sort By</label>
            <select id="sort-filter" 
                    class="filter-select" 
                    name="sort" 
                    aria-labelledby="sort-label"
                    data-filter-type="sort">
                <option value="newest" <?php if($sort === 'newest') echo 'selected'; ?>>Newest First</option>
                <option value="rating" <?php if($sort === 'rating') echo 'selected'; ?>>Highest Rated</option>
                <option value="oldest" <?php if($sort === 'oldest') echo 'selected'; ?>>Oldest First</option>
                <option value="name_asc" <?php if($sort === 'name_asc') echo 'selected'; ?>>Name A-Z</option>
                <option value="name_desc" <?php if($sort === 'name_desc') echo 'selected'; ?>>Name Z-A</option>
            </select>
        </div>

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
                <?php if($style_id && ($style = RecipeAttribute::find_one($style_id, 'style'))) { ?>
                    <span class="filter-tag">
                        Style: <?php echo h($style->name); ?>
                    </span>
                <?php } ?>
                <?php if($diet_id && ($diet = RecipeAttribute::find_one($diet_id, 'diet'))) { ?>
                    <span class="filter-tag">
                        Diet: <?php echo h($diet->name); ?>
                    </span>
                <?php } ?>
                <?php if($type_id && ($type = RecipeAttribute::find_one($type_id, 'type'))) { ?>
                    <span class="filter-tag">
                        Type: <?php echo h($type->name); ?>
                    </span>
                <?php } ?>
                <a href="<?php echo url_for('/recipes/index.php'); ?>" class="clear-filters">
                    <i class="fas fa-times"></i> Clear Filters
                </a>
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
                    <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id)) . '&ref=gallery&gallery_params=' . urlencode(http_build_query($_GET))); ?>" 
                       class="recipe-link"
                       role="link"
                       aria-labelledby="recipe-title-<?php echo h($recipe->recipe_id); ?>">
                        <div class="recipe-image-container">
                            <?php if($session->is_logged_in()) { ?>
                            <button class="favorite-btn <?php echo $recipe->is_favorited ? 'favorited' : ''; ?>"
                                    data-recipe-id="<?php echo h($recipe->recipe_id); ?>"
                                    aria-label="<?php echo $recipe->is_favorited ? 'Remove from' : 'Add to'; ?> favorites">
                                <i class="<?php echo $recipe->is_favorited ? 'fas' : 'far'; ?> fa-heart"></i>
                            </button>
                            <?php } ?>
                            <img src="<?php echo url_for($recipe->get_image_path('thumb')); ?>" 
                                 alt="<?php echo h($recipe->title); ?>" 
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
                                            echo '&#9733;';
                                        }
                                        // Half star if needed
                                        if ($rating - floor($rating) >= 0.5) {
                                            echo '&#189;';
                                        }
                                        // Empty stars
                                        $remaining = 5 - ceil($rating);
                                        for ($i = 1; $i <= $remaining; $i++) {
                                            echo '&#9734;';
                                        }
                                        echo ' <span class="review-count" aria-label="' . $recipe->rating_count() . ' reviews">(' . $recipe->rating_count() . ')</span>';
                                    ?>
                                </span>
                                <span class="time" aria-label="Total time: <?php echo $recipe->get_total_time_display(); ?>">
                                    <?php echo $recipe->get_total_time_display(); ?>
                                </span>
                            </div>

                            <div class="recipe-attributes" role="list">
                                <?php if($style) { ?>
                                    <a href="<?php echo url_for('/recipes/index.php?style=' . h(u($style->id))); ?>" class="recipe-attribute" role="listitem"><?php echo h($style->name); ?></a>
                                <?php } ?>
                                <?php if($diet) { ?>
                                    <a href="<?php echo url_for('/recipes/index.php?diet=' . h(u($diet->id))); ?>" class="recipe-attribute" role="listitem"><?php echo h($diet->name); ?></a>
                                <?php } ?>
                                <?php if($type) { ?>
                                    <a href="<?php echo url_for('/recipes/index.php?type=' . h(u($type->id))); ?>" class="recipe-attribute" role="listitem"><?php echo h($type->name); ?></a>
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

<!-- Load JavaScript files -->
<script src="<?php echo url_for('/assets/js/pages/recipe-gallery.js?v=' . time()); ?>" defer></script>

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
