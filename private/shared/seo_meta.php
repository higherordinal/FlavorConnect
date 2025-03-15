<?php
/**
 * SEO Meta Tags
 * 
 * This file contains centralized SEO meta tags for the FlavorConnect website.
 * It includes meta description, keywords, Open Graph tags, Twitter Card tags,
 * and structured data for recipe pages.
 */

// Default values if not set in the page
if(!isset($page_description)) {
    $page_description = 'Discover and share delicious recipes with FlavorConnect. Connect with other food enthusiasts and find your next favorite meal.';
}

if(!isset($page_keywords)) {
    $page_keywords = 'recipes, cooking, food, meal ideas, recipe sharing, culinary, food community';
}

if(!isset($page_image)) {
    $page_image = 'http://' . $_SERVER['HTTP_HOST'] . url_for('/assets/images/flavorconnect_logo.png');
}

// Output meta tags
?>
<!-- SEO Meta Tags -->
<meta name="description" content="<?php echo h($page_description); ?>">
<meta name="keywords" content="<?php echo h($page_keywords); ?>">
<meta name="author" content="FlavorConnect">
<link rel="canonical" href="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
<meta property="og:title" content="FlavorConnect - <?php echo h($page_title); ?>">
<meta property="og:description" content="<?php echo h($page_description); ?>">
<meta property="og:image" content="<?php echo h($page_image); ?>">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
<meta property="twitter:title" content="FlavorConnect - <?php echo h($page_title); ?>">
<meta property="twitter:description" content="<?php echo h($page_description); ?>">
<meta property="twitter:image" content="<?php echo h($page_image); ?>">

<?php 
// Only include recipe structured data if we have a valid recipe object
// and all required methods and properties are available
if(isset($is_recipe_page) && $is_recipe_page === true && isset($recipe) && is_object($recipe)): 
?>
<!-- Recipe Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Recipe",
  "name": "<?php echo h($recipe->title); ?>",
  "author": {
    "@type": "Person",
    "name": "<?php echo method_exists($recipe, 'author') ? h($recipe->author()->full_name()) : (method_exists($recipe, 'user') ? h($recipe->user()->full_name()) : 'FlavorConnect User'); ?>"
  },
  "datePublished": "<?php echo h($recipe->created_at); ?>",
  "description": "<?php echo h(substr(strip_tags($recipe->description), 0, 160)); ?>",
  "image": "<?php echo 'http://' . $_SERVER['HTTP_HOST'] . url_for($recipe->get_image_path()); ?>",
  "recipeCategory": "<?php echo method_exists($recipe, 'type') && $recipe->type() ? h($recipe->type()->name) : ''; ?>",
  "recipeCuisine": "<?php echo method_exists($recipe, 'style') && $recipe->style() ? h($recipe->style()->name) : ''; ?>",
  "keywords": "<?php echo h($recipe->title) . (method_exists($recipe, 'type') && $recipe->type() ? ', ' . h($recipe->type()->name) : '') . (method_exists($recipe, 'style') && $recipe->style() ? ', ' . h($recipe->style()->name) : '') . (method_exists($recipe, 'diet') && $recipe->diet() ? ', ' . h($recipe->diet()->name) : ''); ?>",
  "recipeYield": "<?php echo isset($recipe->servings) ? h($recipe->servings) . ' servings' : ''; ?>",
  "prepTime": "PT<?php echo isset($recipe->prep_time) ? h($recipe->prep_time) : '0'; ?>M",
  "cookTime": "PT<?php echo isset($recipe->cook_time) ? h($recipe->cook_time) : '0'; ?>M",
  "totalTime": "PT<?php echo isset($recipe->prep_time) && isset($recipe->cook_time) ? h($recipe->prep_time + $recipe->cook_time) : '0'; ?>M"
}
</script>
<?php endif; ?>
