<?php
require_once('../../../private/core/initialize.php');
require_login();

// Only admins and super admins can access this page
if(!$session->is_admin() && !$session->is_super_admin()) {
    $session->message('Access denied. Admin privileges required.');
    redirect_to(url_for('/'));
}

$page_title = 'Admin';
$page_style = 'admin';

// Get all metadata
$styles = RecipeAttribute::find_by_type('style');
$diets = RecipeAttribute::find_by_type('diet');
$types = RecipeAttribute::find_by_type('type');
$measurements = Measurement::find_all_ordered();

include(SHARED_PATH . '/member_header.php');
?>

<link rel="stylesheet" href="<?php echo url_for('/assets/css/pages/admin.css'); ?>">

<div class="admin-management metadata">
    <div class="breadcrumbs">
        <a href="<?php echo url_for('/'); ?>" class="breadcrumb-item">Home</a>
        <span class="breadcrumb-separator">/</span>
        <a href="<?php echo private_url_for('/admin/index.php'); ?>" class="breadcrumb-item">Admin</a>
        <span class="breadcrumb-separator">/</span>
        <span class="breadcrumb-item active">Recipe Metadata</span>
    </div>

    <h1>Recipe Metadata Management</h1>
    
    <?php echo display_session_message(); ?>
    
    <form action="<?php echo url_for('/admin/categories/save.php'); ?>" method="post">
        <div class="metadata-sections">
            <!-- Recipe Styles Section -->
            <section class="metadata-section">
                <h2>Recipe Styles</h2>
                <table class="list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($styles as $style) { ?>
                            <tr>
                                <td>
                                    <input type="text" name="styles[<?php echo h($style->id); ?>]" value="<?php echo h($style->name); ?>" class="form-control">
                                </td>
                                <td class="actions">
                                    <button type="button" class="action delete" data-id="<?php echo h($style->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="new-row">
                            <td>
                                <input type="text" name="new_styles[]" placeholder="Add new style..." class="form-control">
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Recipe Diets Section -->
            <section class="metadata-section">
                <h2>Recipe Diets</h2>
                <table class="list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Recipes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($diets as $diet) { ?>
                            <tr>
                                <td>
                                    <input type="text" name="diets[<?php echo h($diet->id); ?>]" value="<?php echo h($diet->name); ?>" class="form-control">
                                </td>
                                <td><?php echo Recipe::count_by_diet($diet->id); ?></td>
                                <td class="actions">
                                    <button type="button" class="action delete" data-id="<?php echo h($diet->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="new-row">
                            <td>
                                <input type="text" name="new_diets[]" placeholder="Add new diet..." class="form-control">
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Recipe Types Section -->
            <section class="metadata-section">
                <h2>Recipe Types</h2>
                <table class="list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Recipes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($types as $type) { ?>
                            <tr>
                                <td>
                                    <input type="text" name="types[<?php echo h($type->id); ?>]" value="<?php echo h($type->name); ?>" class="form-control">
                                </td>
                                <td><?php echo Recipe::count_by_type($type->id); ?></td>
                                <td class="actions">
                                    <button type="button" class="action delete" data-id="<?php echo h($type->id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="new-row">
                            <td>
                                <input type="text" name="new_types[]" placeholder="Add new type..." class="form-control">
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Recipe Measurements Section -->
            <section class="metadata-section">
                <h2>Recipe Measurements</h2>
                <table class="list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($measurements as $measurement) { ?>
                            <tr>
                                <td>
                                    <input type="text" name="measurements[<?php echo h($measurement->measurement_id); ?>]" value="<?php echo h($measurement->name); ?>" class="form-control">
                                </td>
                                <td class="actions">
                                    <button type="button" class="action delete" data-id="<?php echo h($measurement->measurement_id); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php } ?>
                        <tr class="new-row">
                            <td>
                                <input type="text" name="new_measurements[]" placeholder="Add new measurement..." class="form-control">
                            </td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </div>

        <div class="form-buttons">
            <button type="submit" class="action">Save Changes</button>
        </div>
    </form>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
