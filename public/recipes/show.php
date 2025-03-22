<?php
require_once('../../private/core/initialize.php');

// Get and validate recipe ID
$rules = ['id' => ['required', 'number', 'min:1']];
$errors = validate_api_request(['id' => $_GET['id'] ?? ''], $rules);

if (!empty($errors)) {
    $session->message('Invalid recipe ID.');
    redirect_to(url_for('/recipes/index.php'));
}

$recipe = Recipe::find_by_id($_GET['id']);
if (!$recipe) {
    $session->message('Recipe not found.');
    redirect_to(url_for('/recipes/index.php'));
}

// Set page title and style
$page_title = $recipe->title;
$page_style = 'recipe-show';

// Set SEO variables for recipe structured data
$is_recipe_page = true;
$page_description = substr(strip_tags($recipe->description), 0, 160);
$page_keywords = $recipe->title;
if (method_exists($recipe, 'type') && $recipe->type()) {
    $page_keywords .= ', ' . $recipe->type()->name;
}
if (method_exists($recipe, 'style') && $recipe->style()) {
    $page_keywords .= ', ' . $recipe->style()->name;
}
if (method_exists($recipe, 'diet') && $recipe->diet()) {
    $page_keywords .= ', ' . $recipe->diet()->name;
}
$page_image = 'http://' . $_SERVER['HTTP_HOST'] . url_for($recipe->get_image_path('optimized'));

// Determine back link based on referrer
$ref = $_GET['ref'] ?? '';
$back_text = 'Back to Recipes';

// Use the smart back link function
$back_link = get_back_link('/recipes/index.php');

// Set appropriate back text based on the back link
if (strpos($back_link, '/index.php') !== false) {
    $back_text = 'Back to Home';
} elseif (strpos($back_link, '/users/favorites.php') !== false) {
    $back_text = 'Back to Favorites';
} elseif (strpos($back_link, '/users/profile.php') !== false) {
    $back_text = 'Back to Profile';
}

// Set up breadcrumbs
$breadcrumbs = [
    ['url' => '/index.php', 'label' => 'Home'],
    ['url' => '/recipes/index.php', 'label' => 'Recipes'],
    ['label' => h($recipe->title)]
];

// Handle review deletion by ID (admin only)
if (isset($_GET['action']) && $_GET['action'] === 'admin_delete_review' && isset($_GET['rating_id'])) {
    if (!$session->is_logged_in()) {
        $session->message('You must be logged in to delete a review.');
        redirect_to(url_for('/login.php'));
    }
    
    // Check if user is admin or super admin
    if (!$session->is_admin() && !$session->is_super_admin()) {
        $session->message('You do not have permission to delete this review.');
        redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
    }
    
    $rating_id = $_GET['rating_id'];
    
    // Delete the review
    if (RecipeReview::delete_by_id($rating_id)) {
        $session->message('Review deleted successfully by admin.');
    } else {
        $session->message('Failed to delete review.');
    }
    
    redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
}

// Handle review deletion (user's own reviews)
if (isset($_GET['action']) && $_GET['action'] === 'delete_review') {
    if (!$session->is_logged_in()) {
        $session->message('You must be logged in to delete a review.');
        redirect_to(url_for('/login.php'));
    }
    
    $current_user_id = $session->get_user_id();
    
    // Check if the review exists and belongs to the current user
    $review = RecipeReview::find_by_recipe_and_user($recipe->recipe_id, $current_user_id);
    
    if (!$review) {
        $session->message('Review not found or you do not have permission to delete it.');
        redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
    }
    
    // Delete the review
    if (RecipeReview::delete_review($recipe->recipe_id, $current_user_id)) {
        $session->message('Review deleted successfully.');
    } else {
        $session->message('Failed to delete review.');
    }
    
    redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
}

// Handle new review submission
if (is_post_request()) {
    if (!$session->is_logged_in()) {
        $session->message('You must be logged in to submit a review.');
        redirect_to(url_for('/login.php'));
    }

    $current_user_id = $session->get_user_id();
    
    // Check if user has already reviewed this recipe
    $existing_review = RecipeReview::find_by_sql(
        "SELECT * FROM recipe_rating " .
        "WHERE recipe_id = '" . db_escape($db, $recipe->recipe_id) . "' " .
        "AND user_id = '" . db_escape($db, $current_user_id) . "' LIMIT 1"
    );

    if (!empty($existing_review)) {
        $session->message('You have already reviewed this recipe.');
        redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
    }

    $review_data = [
        'recipe_id' => $recipe->recipe_id,
        'user_id' => $current_user_id,
        'rating_value' => $_POST['review']['rating'] ?? '',
        'comment_text' => $_POST['review']['comment'] ?? ''
    ];

    $errors = validate_recipe_comment($review_data);
    
    if (empty($errors)) {
        $review = new RecipeReview($review_data);
        if ($review->save()) {
            $session->message('Review submitted successfully.');
            redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
        } else {
            $errors[] = 'Failed to save review.';
        }
    } else {
    }
}

// Get all reviews for this recipe
$reviews = RecipeReview::find_by_recipe_id($recipe->recipe_id);

// Get recipe ingredients and steps
$ingredients = $recipe->ingredients();
$steps = $recipe->steps();

// Include the appropriate header based on login status
if($session->is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}

// Check if recipe is favorited by current user
$is_favorited = false;
if($session->is_logged_in()) {
    $is_favorited = RecipeFavorite::is_favorited($session->get_user_id(), $recipe->recipe_id);
}

// Display any validation errors
if(!empty($errors)) {
    echo display_errors($errors);
}

// Display any session messages
echo display_session_message();
?>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-show.css'); ?>">

<div class="recipe-show">
    <div class="container recipe-container">
        <?php 
        echo unified_navigation(
            '/recipes/index.php',
            $breadcrumbs,
            $back_text
        ); 
        ?>
        
        <div class="recipe-header-image">
            <?php if ($recipe->get_image_path('optimized')): ?>
                <img src="<?php echo url_for($recipe->get_image_path('optimized')); ?>" 
                     alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
            <?php else: ?>
                <img src="<?php echo url_for('/assets/images/recipe-placeholder.jpg'); ?>" 
                     alt="<?php echo h($recipe->title); ?>">
            <?php endif; ?>
            <div class="recipe-header-overlay">
                <div class="recipe-header-content">
                    <div class="recipe-title-section">
                        <h1><?php echo h($recipe->title); ?></h1>
                        <?php if($session->is_logged_in()) { ?>
                            <button class="favorite-btn" data-recipe-id="<?php echo $recipe->recipe_id; ?>" aria-label="Add to favorites">
                                <i class="far fa-heart"></i>
                            </button>
                        <?php } ?>
                    </div>
                    <div class="recipe-meta">
                        <div class="recipe-rating">
                            <?php 
                            $avg_rating = $recipe->average_rating();
                            ?>
                            <div class="stars">
                                <?php 
                                if ($avg_rating) {
                                    // Full stars
                                    for ($i = 1; $i <= floor($avg_rating); $i++) {
                                        echo '<i class="fas fa-star"></i>';
                                    }
                                    // Half star if needed
                                    if ($avg_rating - floor($avg_rating) >= 0.5) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    }
                                    // Empty stars
                                    for ($i = ceil($avg_rating); $i < 5; $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                } else {
                                    // No ratings yet
                                    for ($i = 0; $i < 5; $i++) {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="recipe-details">
                            <div class="recipe-time">
                                <span>
                                    <i class="fas fa-clock"></i>
                                    Prep: <?php echo h(TimeUtility::format_time($recipe->prep_time)); ?>
                                </span>
                                <span>
                                    <i class="fas fa-fire"></i>
                                    Cook: <?php echo h(TimeUtility::format_time($recipe->cook_time)); ?>
                                </span>
                                <span>
                                    <i class="fas fa-hourglass-end"></i>
                                    Total: <?php echo h(TimeUtility::format_time($recipe->prep_time + $recipe->cook_time)); ?>
                                </span>
                            </div>
                            <div class="recipe-attributes" role="list">
                                <span role="listitem">
                                    <i class="fas fa-utensils"></i>
                                    <?php echo h($recipe->style() ? $recipe->style()->name : 'Any Style'); ?>
                                </span>
                                <span role="listitem">
                                    <i class="fas fa-leaf"></i>
                                    <?php echo h($recipe->diet() ? $recipe->diet()->name : 'Any Diet'); ?>
                                </span>
                                <span role="listitem">
                                    <i class="fas fa-plate"></i>
                                    <?php echo h($recipe->type() ? $recipe->type()->name : 'Any Type'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if($session->is_logged_in() && ($recipe->user_id == $session->get_user_id() || $session->is_admin())) { ?>
                        <div class="recipe-actions">
                            <a href="<?php echo url_for('/recipes/edit.php?id=' . h(u($recipe->recipe_id)) . '&ref=show'); ?>" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Edit Recipe
                            </a>
                            <a href="<?php echo url_for('/recipes/delete.php?id=' . h(u($recipe->recipe_id)) . '&ref=show'); ?>" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Delete Recipe
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <div class="recipe-description-container">
            <div class="recipe-description">
                <?php echo h($recipe->description); ?>
            </div>
            <button class="print-recipe-btn" id="printRecipeBtn">
                <i class="fas fa-print"></i> Print Recipe
            </button>
        </div>

        <div class="recipe-ingredients">
            <div class="ingredients-header">
                <h2>Ingredients</h2>
                <div class="scaling-buttons">
                    <button class="scale-btn" data-scale="0.5">½×</button>
                    <button class="scale-btn active" data-scale="1">1×</button>
                    <button class="scale-btn" data-scale="2">2×</button>
                    <button class="scale-btn" data-scale="3">3×</button>
                </div>
            </div>
            <ul class="ingredients-list">
                <?php foreach($ingredients as $ingredient) { ?>
                    <li>
                        <span class="amount" data-base="<?php echo h($ingredient->quantity); ?>">
                            <?php echo h(format_quantity($ingredient->quantity)); ?>
                        </span>
                        <?php 
                        $measurement = $ingredient->measurement;
                        $ingredient_obj = $ingredient->ingredient;
                        echo h($measurement ? $measurement->name : ''); ?> 
                        <?php echo h($ingredient_obj ? $ingredient_obj->name : ''); ?>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <div class="recipe-directions">
            <h2>Directions</h2>
            <ol class="directions-list">
                <?php foreach($steps as $step) { ?>
                    <li>
                        <div class="direction-step">
                            <span class="step-number"><?php echo h($step->step_number); ?></span>
                            <div class="step-content">
                                <p><?php echo h($step->instruction); ?></p>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ol>
        </div>

        <?php if($recipe->video_url) { ?>
            <div class="recipe-video">
                <h2>Watch How to Make It</h2>
                <div class="video-container">
                    <?php
                    // Extract video ID from YouTube URL
                    $video_id = '';
                    if(preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $recipe->video_url, $match)) {
                        $video_id = $match[1];
                    }
                    if($video_id) {
                        echo '<iframe width="560" height="315" src="https://www.youtube.com/embed/' . h($video_id) . '" frameborder="0" allowfullscreen></iframe>';
                    }
                    ?>
                </div>
            </div>
        <?php } ?>

        <div class="comments-section">
            <div class="comments-header">
                <h2><i class="fas fa-comments"></i> Reviews & Comments</h2>
                <?php if($reviews) { 
                    $avg_rating = array_reduce($reviews, function($carry, $review) {
                        return $carry + $review->rating_value;
                    }, 0) / count($reviews);
                ?>
                    <div class="rating-summary">
                        <div class="average-rating">
                            <span class="rating-value"><?php echo number_format($avg_rating, 1); ?></span>
                            <div class="stars">
                                <?php 
                                for($i = 1; $i <= 5; $i++) {
                                    if ($avg_rating >= $i) {
                                        echo '<i class="fas fa-star"></i>';
                                    } elseif ($avg_rating > $i - 1) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="rating-count">(<?php echo count($reviews); ?> reviews)</span>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <?php if($session->is_logged_in()) { ?>
                <div class="add-comment">
                    <h3>Add Your Review</h3>
                    <form action="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id))); ?>" method="post">
                        <fieldset>
                            <legend class="visually-hidden">Review Form</legend>
                            <div class="form-group rating-input">
                                <fieldset>
                                    <legend>Rating:</legend>
                                    <div class="star-rating">
                                        <input type="radio" id="star5" name="review[rating]" value="5" required>
                                        <label for="star5"><i class="fas fa-star"></i><span class="visually-hidden">5 stars</span></label>
                                        <input type="radio" id="star4" name="review[rating]" value="4">
                                        <label for="star4"><i class="fas fa-star"></i><span class="visually-hidden">4 stars</span></label>
                                        <input type="radio" id="star3" name="review[rating]" value="3">
                                        <label for="star3"><i class="fas fa-star"></i><span class="visually-hidden">3 stars</span></label>
                                        <input type="radio" id="star2" name="review[rating]" value="2">
                                        <label for="star2"><i class="fas fa-star"></i><span class="visually-hidden">2 stars</span></label>
                                        <input type="radio" id="star1" name="review[rating]" value="1">
                                        <label for="star1"><i class="fas fa-star"></i><span class="visually-hidden">1 star</span></label>
                                    </div>
                                </fieldset>
                            </div>
                            
                            <div class="form-group comment-input">
                                <label for="comment">Your Comment (optional):</label>
                                <textarea 
                                    id="comment" 
                                    name="review[comment]" 
                                    rows="4" 
                                    placeholder="Share your thoughts about this recipe..."
                                ></textarea>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" aria-label="Submit your review">Submit Review</button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            <?php } else { ?>
                <div class="login-prompt">
                    <p>Please <a href="<?php echo url_for('/auth/login.php'); ?>">log in</a> to leave a review.</p>
                </div>
            <?php } ?>

            <div class="comments-list">
                <?php 
                foreach($reviews as $review) { 
                    $user = $review->user();
                ?>
                    <div class="comment">
                        <div class="comment-header">
                            <span class="comment-author">
                                <i class="fas fa-user"></i>
                                <?php echo h($review->username ?? 'Anonymous'); ?>
                            </span>
                            <div class="rating-display">
                                <?php 
                                for($i = 1; $i <= 5; $i++) {
                                    if ($review->rating_value >= $i) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <span class="comment-date">
                                <i class="fas fa-clock"></i>
                                <?php echo h(date('M j, Y g:i A', strtotime($review->comment_created_at ?? 'now'))); ?>
                            </span>
                            <?php if($session->is_logged_in() && $session->get_user_id() == $review->user_id) { ?>
                                <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id)) . '&action=delete_review'); ?>" class="delete-comment" aria-label="Delete your review">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php } elseif($session->is_logged_in() && ($session->is_admin() || $session->is_super_admin())) { ?>
                                <a href="<?php echo url_for('/recipes/show.php?id=' . h(u($recipe->recipe_id)) . '&action=admin_delete_review&rating_id=' . h(u($review->rating_id))); ?>" class="delete-comment" aria-label="Delete this review (admin)">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            <?php } ?>
                        </div>
                        <?php 
                        $comment_text = trim($review->comment_text ?? '');
                        if(!empty($comment_text)) { 
                        ?>
                            <div class="comment-content">
                                <?php echo h($comment_text); ?>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<script>
    window.initialConfig = {
        baseUrl: '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . url_for('/api/recipes/'); ?>'
    };
    
    window.initialUserData = <?php echo json_encode([
        'isLoggedIn' => $session->is_logged_in(),
        'userId' => $session->get_user_id(),
        'apiBaseUrl' => 'http://localhost:3000'
    ]); ?>;
</script>

<script id="recipe-data-json" type="application/json">
<?php 
    $recipe_data = [
        'recipe_id' => (int)$recipe->recipe_id,
        'user_id' => (int)$session->get_user_id(),
        'title' => $recipe->title,
        'description' => $recipe->description,
        'is_favorited' => (bool)$recipe->is_favorited_by_user($session->get_user_id()),
        'comments' => array_map(function($review) {
            return [
                'id' => (int)$review->rating_id,
                'user_name' => $review->user()->full_name(),
                'rating' => (int)$review->rating_value,
                'comment_text' => $review->comment_text,
                'created_at' => $review->created_at
            ];
        }, $reviews ?? [])
    ];
    echo json_encode($recipe_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); 
?>
</script>

<script src="<?php echo url_for('/assets/js/pages/recipe-scale.js'); ?>?v=<?php echo time(); ?>"></script>
<script src="<?php echo url_for('/assets/js/utils/favorites.js'); ?>?v=<?php echo time(); ?>"></script>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const favoriteBtn = document.querySelector('.favorite-btn');
        if (favoriteBtn) {
            const recipeId = favoriteBtn.dataset.recipeId;
            
            // Check initial favorite status
            if (typeof window.checkFavoriteStatus === 'function') {
                const isFavorited = await window.checkFavoriteStatus(recipeId);
                if (isFavorited) {
                    favoriteBtn.classList.add('favorited');
                    favoriteBtn.querySelector('i').classList.remove('far');
                    favoriteBtn.querySelector('i').classList.add('fas');
                    favoriteBtn.setAttribute('aria-label', 'Remove from favorites');
                }
            }
            
            // Initialize favorite button functionality
            if (typeof window.initializeFavoriteButtons === 'function') {
                window.initializeFavoriteButtons();
            }
        }
    });
</script>

<script>
    // Print button functionality
    document.addEventListener('DOMContentLoaded', function() {
        const printBtn = document.getElementById('printRecipeBtn');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                window.print();
            });
        }
    });
</script>

<script src="<?php echo url_for('/assets/js/pages/recipe-show.js'); ?>?v=<?php echo time(); ?>"></script>

<?php include(SHARED_PATH . '/footer.php'); ?>