<?php
require_once('../../private/core/initialize.php');

// Get recipe ID from URL
$id = $_GET['id'] ?? '1';
$recipe = Recipe::find_by_id($id);
if(!$recipe) {
    redirect_to(url_for('/index.php'));
}

// Determine back link based on referrer
$ref = $_GET['ref'] ?? '';
$back_link = $ref === 'home' ? url_for('/index.php') : url_for('/recipes/index.php');
$back_text = $ref === 'home' ? 'Back to Home' : 'Back to Recipes';

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

$page_title = 'recipe-show';
$page_style = 'recipe-show';

// Include the appropriate header based on login status
if($session->is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}

// Prepare recipe data for JavaScript
$recipe_data = [
    'recipe_id' => $recipe->recipe_id,
    'title' => $recipe->title,
    'description' => $recipe->description,
    'prep_time' => $recipe->prep_time,
    'cook_time' => $recipe->cook_time,
    'img_file_path' => $recipe->img_file_path,
    'video_url' => $recipe->video_url,
    'alt_text' => $recipe->alt_text,
    'ingredients' => array_map(function($ing) {
        return [
            'ingredient_id' => $ing->ingredient_id,
            'name' => $ing->name,
            'amount' => $ing->amount,
            'unit' => $ing->unit
        ];
    }, $ingredients),
    'steps' => array_map(function($step) {
        return [
            'step_id' => $step->step_id,
            'instruction' => $step->instruction,
            'step_number' => $step->step_number
        ];
    }, $steps)
];
?>

<script>
    // Make recipe data available to JavaScript
    window.recipeData = <?php echo json_encode($recipe_data); ?>;
</script>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/recipe-show.css'); ?>">

<div class="recipe-show">
    <a href="<?php echo $back_link; ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> <?php echo $back_text; ?>
    </a>
    <div class="recipe-header-image">
        <img src="<?php echo url_for('/assets/images/recipe-placeholder.jpg'); ?>" 
             alt="<?php echo h($recipe->alt_text ?? $recipe->title); ?>">
        <div class="recipe-header-overlay">
            <h1><?php echo h($recipe->title); ?></h1>
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
                        // Show empty stars if no ratings
                        for ($i = 0; $i < 5; $i++) {
                            echo '<i class="far fa-star"></i>';
                        }
                    }
                    ?>
                </div>
                <?php if ($avg_rating): ?>
                    <span class="rating-text"><?php echo h($avg_rating . ' / 5.0'); ?></span>
                <?php endif; ?>
            </div>
            <div class="recipe-meta">
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

<script src="<?php echo url_for('/assets/js/pages/recipe-scale.js'); ?>?v=<?php echo time(); ?>" type="module"></script>
<script src="<?php echo url_for('/assets/js/pages/recipe-show.js'); ?>?v=<?php echo time(); ?>" type="module"></script>
<?php include(SHARED_PATH . '/footer.php'); ?>