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

// Determine back link based on referrer
$ref = $_GET['ref'] ?? '';
switch ($ref) {
    case 'home':
        $back_link = url_for('/index.php');
        $back_text = 'Back to Home';
        break;
    case 'favorites':
        $back_link = private_url_for('/users/favorites.php');
        $back_text = 'Back to Favorites';
        break;
    case 'profile':
        $back_link = private_url_for('/users/profile.php');
        $back_text = 'Back to Profile';
        break;
    case 'gallery':
        $back_link = url_for('/recipes/index.php');
        $back_text = 'Back to Recipes';
        break;
    default:
        $back_link = url_for('/recipes/index.php');
        $back_text = 'Back to Recipes';
}

// Handle new review submission
if (is_post_request()) {
    if (!$session->is_logged_in()) {
        $session->message('You must be logged in to submit a review.');
        redirect_to(url_for('/login.php'));
    }

    $review_data = [
        'recipe_id' => $recipe->recipe_id,
        'user_id' => $session->get_user_id(),
        'rating_value' => $_POST['review']['rating'] ?? '',
        'comment_text' => $_POST['review']['comment'] ?? ''
    ];

    $errors = validate_recipe_comment($review_data);
    
    if (empty($errors)) {
        $review = new Review($review_data);
        if ($review->save()) {
            $session->message('Review submitted successfully.');
            redirect_to(url_for('/recipes/show.php?id=' . $recipe->recipe_id));
        } else {
            $errors[] = 'Failed to save review.';
        }
    }
}

// Get all reviews for this recipe
$reviews = Review::find_by_recipe_id($recipe->recipe_id);

// Get recipe ingredients and steps
$ingredients = $recipe->ingredients();
$steps = $recipe->steps();

$page_title = 'recipe-show';
$page_style = 'recipe-show';

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
    <a href="<?php echo $back_link; ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> <?php echo $back_text; ?>
    </a>
    <div class="recipe-header-image">
        <img src="<?php echo url_for($recipe->get_image_path()); ?>" 
             alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
        <div class="recipe-header-overlay">
            <div class="recipe-header-content">
                <div class="recipe-title-section">
                    <h1><?php echo h($recipe->title); ?></h1>
                    <?php if($session->is_logged_in()) { ?>
                        <button class="favorite-btn" data-recipe-id="<?php echo $recipe->recipe_id; ?>">
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
                        <div class="recipe-attributes">
                            <span>
                                <i class="fas fa-utensils"></i>
                                <?php echo h($recipe->style() ? $recipe->style()->name : 'Any Style'); ?>
                            </span>
                            <span>
                                <i class="fas fa-leaf"></i>
                                <?php echo h($recipe->diet() ? $recipe->diet()->name : 'Any Diet'); ?>
                            </span>
                            <span>
                                <i class="fas fa-plate"></i>
                                <?php echo h($recipe->type() ? $recipe->type()->name : 'Any Type'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <?php if($session->is_logged_in() && ($recipe->user_id == $session->get_user_id() || $session->is_admin())) { ?>
                    <div class="recipe-actions">
                        <a href="<?php echo private_url_for('/recipes/edit.php?id=' . h(u($recipe->recipe_id))); ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Edit Recipe
                        </a>
                        <a href="<?php echo private_url_for('/recipes/delete.php?id=' . h(u($recipe->recipe_id))); ?>" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Delete Recipe
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>

    <div class="recipe-description">
        <?php echo h($recipe->description); ?>
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
                    <div class="form-group rating-input">
                        <label>Rating:</label>
                        <div class="star-rating">
                            <input type="radio" id="star1" name="review[rating]" value="1">
                            <label for="star1"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star2" name="review[rating]" value="2">
                            <label for="star2"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star3" name="review[rating]" value="3">
                            <label for="star3"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star4" name="review[rating]" value="4">
                            <label for="star4"><i class="fas fa-star"></i></label>
                            <input type="radio" id="star5" name="review[rating]" value="5">
                            <label for="star5"><i class="fas fa-star"></i></label>
                        </div>
                    </div>
                    
                    <div class="form-group comment-input">
                        <label for="comment">Your Comment:</label>
                        <textarea 
                            id="comment" 
                            name="review[comment]" 
                            rows="4" 
                            required 
                            placeholder="Share your thoughts about this recipe..."
                        ></textarea>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Submit Review</button>
                    </div>
                </form>
            </div>
        <?php } else { ?>
            <div class="login-prompt">
                <p>Please <a href="<?php echo url_for('/auth/login.php'); ?>">log in</a> to leave a review.</p>
            </div>
        <?php } ?>

        <div class="comments-list">
            <?php foreach($reviews as $review) { 
                $user = $review->user();
            ?>
                <div class="comment">
                    <div class="comment-header">
                        <span class="comment-author">
                            <i class="fas fa-user"></i>
                            <?php echo h($user ? $user->username : 'Anonymous'); ?>
                        </span>
                        <?php if($review->created_at) { ?>
                            <span class="comment-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M j, Y', strtotime($review->created_at)); ?>
                            </span>
                        <?php } ?>
                        <div class="comment-rating">
                            <?php 
                            $rating = $review->rating_value;
                            for($i = 1; $i <= 5; $i++) {
                                if ($rating >= $i) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($rating > $i - 1) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                    </div>
                    <?php if($review->comment_text) { ?>
                        <div class="comment-text">
                            <?php echo h($review->comment_text); ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
    window.initialConfig = {
        baseUrl: '<?php echo 'http://' . $_SERVER['HTTP_HOST'] . url_for('/api/recipes/'); ?>'
    };
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

<script src="<?php echo url_for('/assets/js/pages/recipe-scale.js'); ?>?v=<?php echo time(); ?>" type="module"></script>

<script>
    window.initialUserData = <?php echo json_encode([
        'isLoggedIn' => $session->is_logged_in(),
        'userId' => $session->get_user_id(),
        'apiBaseUrl' => 'http://localhost:3000'
    ]); ?>;
</script>

<script type="module">
    import { initializeFavoriteButtons, checkFavoriteStatus } from '<?php echo url_for('/assets/js/utils/favorites.js'); ?>';
    
    document.addEventListener('DOMContentLoaded', async () => {
        const favoriteBtn = document.querySelector('.favorite-btn');
        if (favoriteBtn) {
            const recipeId = favoriteBtn.dataset.recipeId;
            
            // Check initial favorite status
            const isFavorited = await checkFavoriteStatus(recipeId);
            if (isFavorited) {
                favoriteBtn.classList.add('favorited');
                favoriteBtn.querySelector('i').classList.remove('far');
                favoriteBtn.querySelector('i').classList.add('fas');
            }
            
            // Initialize favorite button functionality
            initializeFavoriteButtons();
        }
    });
</script>

<script src="<?php echo url_for('/assets/js/pages/recipe-show.js'); ?>?v=<?php echo time(); ?>" type="module"></script>

<?php include(SHARED_PATH . '/footer.php'); ?>