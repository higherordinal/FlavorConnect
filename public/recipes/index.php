<?php
require_once('../../private/core/initialize.php');

$page_title = 'Recipes';
$page_style = 'recipe-gallery';
$component_styles = ['recipe-favorite', 'pagination', 'forms'];

if($session->is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}
?>

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

// Create pagination object
$pagination = new Pagination($current_page, $per_page, $total_recipes);

// Get recipes for current page
$recipes = $recipe_filter->apply();

// Ensure current page is not greater than total pages
if ($current_page > $pagination->total_pages()) {
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
                <?php 
                    // Set variables for the recipe card component
                    $ref = 'gallery';
                    $gallery_params = urlencode(http_build_query($_GET));
                    
                    // Include the recipe card component
                    include('recipe-card.php'); 
                ?>
            <?php } ?>
        </div>

        <?php if($pagination->total_pages() > 1) { ?>
            <!-- Pagination Controls -->
            <?php 
            // Add filter parameters to preserve filters
            $extra_params = [
                'search' => $search,
                'style' => $style_id,
                'diet' => $diet_id,
                'type' => $type_id,
                'sort' => $sort
            ];
            
            // Check if we can use the route_links method with named routes
            if (function_exists('route')) {
                // Use route_links with the 'recipes.index' named route
                try {
                    echo $pagination->route_links('recipes.index', [], 'page', $extra_params);
                } catch (Exception $e) {
                    // Fallback to traditional method if route_links fails
                    $url_pattern = url_for('/recipes/index.php') . '?page={page}';
                    echo $pagination->page_links($url_pattern, $extra_params);
                }
            } else {
                // Fallback to traditional method
                $url_pattern = url_for('/recipes/index.php') . '?page={page}';
                echo $pagination->page_links($url_pattern, $extra_params);
            }
            
            // Display total records info
            echo '<div class="records-info">Showing ' . count($recipes) . ' of ' . $total_recipes . ' total recipes</div>';
            ?>
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
