<?php
require_once('../../private/core/initialize.php');

require_login();

$page_title = 'User Favorites';
$page_style = 'recipe-gallery';
$component_styles = ['recipe-favorite', 'pagination', 'forms'];

// Scripts
$utility_scripts = ['common', 'back-link'];
$component_scripts = ['member-header', 'recipe-favorite'];
$page_scripts = ['user-favorites'];

// Get current page
$current_page = $_GET['page'] ?? 1;
$current_page = max(1, (int)$current_page);

// Set recipes per page
$per_page = 12;

// Calculate offset
$offset = ($current_page - 1) * $per_page;

// Get total count of user's favorite recipes
$total_favorites = RecipeFavorite::count_by_user_id($session->get_user_id());

// Create pagination object
$pagination = new Pagination($current_page, $per_page, $total_favorites);

// Get paginated favorite recipes
$favorites = Recipe::find_favorites_by_user_id($session->get_user_id(), $per_page, $offset);

include(SHARED_PATH . '/member_header.php');
?>

<div class="container">
    <?php 
    echo unified_navigation(
        '/recipes/index.php',
        [
            ['url' => '/index.php', 'label' => 'Home'],
            ['label' => 'My Favorites']
        ],
        'Back to Recipes'
    ); 
    ?>
    
    <div class="recipe-gallery">
        <div class="gallery-header">
            <h1 class="gallery-title">Your Favorite Recipes</h1>
        </div>

        <?php if(empty($favorites)) { ?>
            <div class="no-recipes">
                <p>You haven't favorited any recipes yet. Browse our recipes and click the heart icon to add them to your favorites!</p>
                <a href="<?php echo url_for('/recipes/index.php'); ?>" class="btn btn-primary">Browse Recipes</a>
            </div>
        <?php } else { ?>
            <div class="recipe-grid">
                <?php foreach($favorites as $recipe) { 
                    $style = $recipe->style();
                    $diet = $recipe->diet();
                    $type = $recipe->type();
                    $rating = $recipe->get_average_rating();
                    $total_time = TimeUtility::format_time($recipe->prep_time + $recipe->cook_time);
                    
                    // Set is_favorited to true since these are from the favorites page
                    $recipe->is_favorited = true;
                ?>
                    <?php 
                        // Set variables for the recipe card component
                        $ref = 'favorites';
                        
                        // Include the recipe card component
                        include('../recipes/recipe-card.php'); 
                    ?>
                <?php } ?>
            </div>
            
            <?php if($pagination->total_pages() > 1) { ?>
                <!-- Pagination Controls -->
                <?php 
                // Check if we can use the route_links method with named routes
                if (function_exists('route')) {
                    // Use route_links with the 'users.favorites' named route
                    try {
                        echo $pagination->route_links('users.favorites', [], 'page');
                    } catch (Exception $e) {
                        // Fallback to traditional method if route_links fails
                        $url_pattern = url_for('/users/favorites.php') . '?page={page}';
                        echo $pagination->page_links($url_pattern);
                    }
                } else {
                    // Fallback to traditional method
                    $url_pattern = url_for('/users/favorites.php') . '?page={page}';
                    echo $pagination->page_links($url_pattern);
                }
                
                // Display total records info
                echo '<div class="records-info">Showing ' . count($favorites) . ' of ' . $total_favorites . ' total favorites</div>';
                ?>
            <?php } ?>
        <?php } ?>
    </div>

    <?php
    // Prepare data for JavaScript
    $favoritesData = array_map(function($recipe) use ($session) {
        return [
            'recipe_id' => $recipe->recipe_id,
            'user_id' => $session->get_user_id()
        ];
    }, $favorites);

    $userData = [
        'isLoggedIn' => $session->is_logged_in(),
        'userId' => $session->get_user_id(),
        'apiBaseUrl' => 'https://flavorconnect-api-f240ecae55b2.herokuapp.com'
    ];
    ?>

    <script>
        // Initialize data for JavaScript
        window.initialUserData = <?php echo json_encode($userData); ?>;
        window.initialFavoritesData = <?php echo json_encode($favoritesData); ?>;
    </script>


    <?php include(SHARED_PATH . '/footer.php'); ?>
