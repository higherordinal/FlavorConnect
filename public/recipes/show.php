<?php
require_once('../../private/core/initialize.php');

// Get recipe ID from URL
$id = $_GET['id'] ?? '1';
$recipe = Recipe::find_by_id($id);
if(!$recipe) {
    redirect_to(url_for('/index.php'));
}

// Handle new review submission
if(is_post_request() && $session->is_logged_in()) {
    $args = [];
    $args['recipe_id'] = $recipe->recipe_id;
    $args['user_id'] = $session->get_user_id();
    $args['rating_value'] = $_POST['review']['rating'] ?? '';
    $args['comment_text'] = $_POST['review']['comment'] ?? '';
    
    $review = new Review($args);
    if($review->save()) {
        $session->message('Review submitted successfully.');
        redirect_to(url_for('/recipes/show.php?id=' . $id));
    } else {
        // Keep errors for display
    }
}

// Get all reviews for this recipe
$reviews = Review::find_by_recipe_id($recipe->recipe_id);

// Get recipe ingredients and steps using Recipe class methods
$ingredients = $recipe->ingredients();
$steps = $recipe->steps();

$page_title = $recipe->title;
$page_style = 'recipe-show';

// Include the appropriate header based on login status
if($session->is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}
?>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-show.css'); ?>">

<div class="recipe-show">
    <a href="<?php echo url_for('/recipes/index.php'); ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Recipes
    </a>
    <div class="recipe-header-image">
        <img src="<?php echo url_for('/assets/images/recipe-placeholder.jpg'); ?>" 
             alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
        <div class="recipe-header-overlay">
            <h1><?php echo h($recipe->title); ?></h1>
            <div class="recipe-meta">
                <div class="recipe-time">
                    <div class="time-item">
                        <span class="time-label">Prep Time:</span>
                        <span class="time-value"><?php echo h($recipe->get_prep_time_display()); ?></span>
                    </div>
                    <div class="time-item">
                        <span class="time-label">Cook Time:</span>
                        <span class="time-value"><?php echo h($recipe->get_cook_time_display()); ?></span>
                    </div>
                    <div class="time-item">
                        <span class="time-label">Total Time:</span>
                        <span class="time-value"><?php echo h($recipe->get_total_time_display()); ?></span>
                    </div>
                </div>
                <span>
                    <i class="fas fa-utensils"></i>
                    <?php echo h($recipe->style()->name ?? 'Any Style'); ?>
                </span>
                <span>
                    <i class="fas fa-leaf"></i>
                    <?php echo h($recipe->diet()->name ?? 'Any Diet'); ?>
                </span>
                <span>
                    <i class="fas fa-tag"></i>
                    <?php echo h($recipe->type()->name ?? 'Any Type'); ?>
                </span>
            </div>
            <?php if($session->is_logged_in() && ($recipe->user_id == $session->get_user_id() || $session->is_admin())) { ?>
                <div class="recipe-actions">
                    <a href="<?php echo url_for('/private/recipes/edit.php?id=' . h(u($recipe->recipe_id))); ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Recipe
                    </a>
                    <a href="<?php echo url_for('/private/recipes/delete.php?id=' . h(u($recipe->recipe_id))); ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Recipe
                    </a>
                </div>
            <?php } ?>
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
                        <?php echo h($ingredient->quantity); ?>
                    </span>
                    <?php echo h($ingredient->get_measurement()->name); ?> 
                    <?php echo h($ingredient->get_ingredient()->name); ?>
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
                // Convert YouTube URL to embed URL
                $video_id = '';
                if(preg_match('/[?&]v=([^&]+)/', $recipe->video_url, $matches)) {
                    $video_id = $matches[1];
                } elseif(preg_match('/youtu\.be\/([^?&]+)/', $recipe->video_url, $matches)) {
                    $video_id = $matches[1];
                }
                if($video_id) {
                    echo '<iframe src="https://www.youtube.com/embed/' . h($video_id) . '" 
                            frameborder="0" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen></iframe>';
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
                    return $carry + (int)$review->rating_value;
                }, 0) / count($reviews);
            ?>
                <div class="average-rating">
                    <span class="stars">
                        <?php for($i = 1; $i <= 5; $i++) { ?>
                            <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'filled' : ''; ?>"></i>
                        <?php } ?>
                    </span>
                    <?php echo number_format($avg_rating, 1); ?> / 5
                    (<?php echo count($reviews); ?> reviews)
                </div>
            <?php } ?>
        </div>

        <?php if($session->is_logged_in()) { ?>
            <form action="<?php echo url_for('/recipes/show.php?id=' . h(u($id))); ?>" 
                  method="POST" 
                  class="comment-form">
                <h3><i class="fas fa-pen"></i> Write a Review</h3>
                <div class="star-rating">
                    <input type="radio" id="star5" name="review[rating]" value="5">
                    <label for="star5" title="5 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star4" name="review[rating]" value="4">
                    <label for="star4" title="4 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star3" name="review[rating]" value="3">
                    <label for="star3" title="3 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star2" name="review[rating]" value="2">
                    <label for="star2" title="2 stars"><i class="fas fa-star"></i></label>
                    <input type="radio" id="star1" name="review[rating]" value="1">
                    <label for="star1" title="1 star"><i class="fas fa-star"></i></label>
                </div>

                <textarea name="review[comment]" 
                          class="comment-textarea" 
                          placeholder="Share your thoughts about this recipe..."
                          required></textarea>

                <button type="submit" class="submit-comment">
                    <i class="fas fa-paper-plane"></i> Submit Review
                </button>
            </form>
        <?php } else { ?>
            <div class="login-prompt">
                <i class="fas fa-lock"></i>
                Please <a href="<?php echo url_for('/public/auth/login.php'); ?>">log in</a> 
                to leave a review.
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
                            <?php echo h($review->user()->username); ?>
                        </span>
                        <?php if($review->created_date) { ?>
                            <span class="comment-date">
                                <i class="fas fa-calendar"></i>
                                <?php echo h($review->created_date); ?>
                            </span>
                        <?php } ?>
                    </div>
                    <div class="comment-rating">
                        <?php for($i = 1; $i <= 5; $i++) { ?>
                            <span class="star <?php echo $i <= $review->rating_value ? 'filled' : ''; ?>">
                                ★
                            </span>
                        <?php } ?>
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

<script src="<?php echo url_for('/assets/js/recipe-scale.js'); ?>"></script>
<script src="<?php echo url_for('/assets/js/recipe-show.js'); ?>"></script>
<?php include(SHARED_PATH . '/footer.php'); ?>