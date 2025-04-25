<?php
/**
 * Recipe Card Component
 * 
 * This component displays a recipe card with image, title, rating, time, attributes, and author.
 * 
 * Required variables:
 * - $recipe: Recipe object
 * 
 * Optional variables:
 * - $ref_page: Reference page path (e.g., '/index.php', '/recipes/index.php') - default is current page
 * - $gallery_params: URL-encoded query parameters for returning to gallery with filters
 */

// Ensure recipe is provided
if (!isset($recipe) || !is_object($recipe)) {
    return;
}

// Set default reference page if not provided
if (!isset($ref_page)) {
    $ref_page = $_SERVER['PHP_SELF'] ?? '/recipes/index.php';
}

// Get related data
$style = $recipe->style();
$diet = $recipe->diet();
$type = $recipe->type();
$rating = $recipe->get_average_rating();
$total_time = TimeUtility::format_time($recipe->prep_time + $recipe->cook_time);
$user = User::find_by_id($recipe->user_id);

// Build URL with appropriate parameters
$url_params = 'id=' . h(u($recipe->recipe_id));

// Add ref_page parameter
$ref_param = '';
if (isset($ref_page)) {
    $ref_param = '&ref_page=' . h(u($ref_page));
    
    // Add gallery parameters if available
    if (isset($gallery_params) && strpos($ref_page, '/recipes/index.php') !== false) {
        // Make sure we're not double-encoding
        $decoded_params = urldecode($gallery_params);
        $ref_param .= '&gallery_params=' . h(u($decoded_params));
    }
}

// Add the ref parameter to the URL
$url_params .= $ref_param;
?>

<article class="recipe-card">
    <div class="recipe-image-container">
        <?php if($session->is_logged_in()) { ?>
            <button class="favorite-btn <?php echo $recipe->is_favorited ? 'favorited' : ''; ?>"
                    data-recipe-id="<?php echo h($recipe->recipe_id); ?>"
                    aria-label="<?php echo $recipe->is_favorited ? 'Remove from' : 'Add to'; ?> favorites">
                <i class="<?php echo $recipe->is_favorited ? 'fas' : 'far'; ?> fa-heart" aria-hidden="true"></i>
            </button>
        <?php } ?>
        <a href="<?php echo url_for('/recipes/show.php?' . $url_params); ?>" 
           class="image-link"
           aria-labelledby="recipe-title-<?php echo h($recipe->recipe_id); ?>">
            <?php if($recipe->get_image_path('thumb')) { ?>
                <img src="<?php echo url_for($recipe->get_image_path('thumb')); ?>" 
                     alt="<?php echo h($recipe->alt_text ?: $recipe->title); ?>" 
                     class="recipe-image">
            <?php } else { ?>
                <img src="<?php echo url_for('/assets/images/recipe-placeholder.png'); ?>" 
                     alt="Recipe placeholder image" 
                     class="recipe-image">
            <?php } ?>
        </a>
    </div>
    
    <div class="recipe-content">
        <a href="<?php echo url_for('/recipes/show.php?' . $url_params); ?>" 
           class="title-link"
           aria-labelledby="recipe-title-<?php echo h($recipe->recipe_id); ?>">
            <h2 class="recipe-title" id="recipe-title-<?php echo h($recipe->recipe_id); ?>"><?php echo h($recipe->title); ?></h2>
        </a>
        
        <div class="recipe-meta">
            <span class="rating" aria-label="Rating: <?php echo $rating; ?> out of 5 stars">
                <?php 
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
            <span class="time" aria-label="Total time: <?php echo $total_time; ?>">
                <i class="far fa-clock" aria-hidden="true"></i>&nbsp;<?php echo $total_time; ?>
            </span>
        </div>

        <div class="recipe-attributes-wrapper">
            <h3 class="visually-hidden">Recipe Attributes</h3>
            <ul class="recipe-attributes">
                <?php if($style) { ?>
                <li>
                    <a href="<?php echo url_for('/recipes/index.php?style=' . h(u($style->id)) . get_ref_parameter('ref_page')); ?>" class="recipe-attribute"><?php echo h($style->name); ?></a>
                </li>
                <?php } ?>
                <?php if($diet) { ?>
                <li>
                    <a href="<?php echo url_for('/recipes/index.php?diet=' . h(u($diet->id)) . get_ref_parameter('ref_page')); ?>" class="recipe-attribute"><?php echo h($diet->name); ?></a>
                </li>
                <?php } ?>
                <?php if($type) { ?>
                <li>
                    <a href="<?php echo url_for('/recipes/index.php?type=' . h(u($type->id)) . get_ref_parameter('ref_page')); ?>" class="recipe-attribute"><?php echo h($type->name); ?></a>
                </li>
                <?php } ?>
            </ul>
        </div>

        <div class="recipe-footer">
            <div class="recipe-author">
                <?php if($user) { ?>
                    <span class="author-name">By <?php echo h($user->username); ?></span>
                <?php } else { ?>
                    <span class="author-name">By Unknown</span>
                <?php } ?>
            </div>
        </div>
    </div>
</article>
