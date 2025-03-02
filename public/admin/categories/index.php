<?php
require_once('../../../private/core/initialize.php');
require_login();
require_admin();

$page_title = 'Admin: Recipe Metadata Management';
$page_style = 'admin';

// Get all metadata
$styles = RecipeAttribute::find_by_type('style');
$diets = RecipeAttribute::find_by_type('diet');
$types = RecipeAttribute::find_by_type('type');
$measurements = Measurement::find_all_ordered();

include(SHARED_PATH . '/member_header.php');
?>

<main class="main-content">
    <div class="admin-management metadata">
        <a href="<?php echo url_for('/admin/index.php'); ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Admin Dashboard
        </a>

        <div class="breadcrumbs">
            <a href="<?php echo url_for('/'); ?>" class="breadcrumb-item">Home</a>
            <span class="breadcrumb-separator">/</span>
            <a href="<?php echo url_for('/admin/index.php'); ?>" class="breadcrumb-item">Admin</a>
            <span class="breadcrumb-separator">/</span>
            <span class="breadcrumb-item active">Recipe Metadata</span>
        </div>

        <div class="admin-header">
            <h1>Recipe Metadata Management</h1>
        </div>
        
        <?php echo display_session_message(); ?>
        
        <div class="metadata-sections">
            <!-- Recipe Styles Section -->
            <section class="metadata-section">
                <div class="section-header">
                    <h2>Recipe Styles</h2>
                </div>
                <table class="list">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Recipes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($styles as $style) { ?>
                            <tr>
                                <td data-label="Name"><?php echo h($style->name); ?></td>
                                <td data-label="Recipes"><?php echo Recipe::count_by_style($style->id); ?></td>
                                <td data-label="Actions" class="actions">
                                    <a href="<?php echo url_for('/admin/categories/style/edit.php?id=' . h(u($style->id))); ?>" class="action edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo url_for('/admin/categories/style/delete.php?id=' . h(u($style->id))); ?>" class="action delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="add-new-button">
                    <a href="<?php echo url_for('/admin/categories/style/new.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Style
                    </a>
                </div>
            </section>

            <!-- Recipe Diets Section -->
            <section class="metadata-section">
                <div class="section-header">
                    <h2>Recipe Diets</h2>
                </div>
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
                                <td data-label="Name"><?php echo h($diet->name); ?></td>
                                <td data-label="Recipes"><?php echo Recipe::count_by_diet($diet->id); ?></td>
                                <td data-label="Actions" class="actions">
                                    <a href="<?php echo url_for('/admin/categories/diet/edit.php?id=' . h(u($diet->id))); ?>" class="action edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo url_for('/admin/categories/diet/delete.php?id=' . h(u($diet->id))); ?>" class="action delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="add-new-button">
                    <a href="<?php echo url_for('/admin/categories/diet/new.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Diet
                    </a>
                </div>
            </section>

            <!-- Recipe Types Section -->
            <section class="metadata-section">
                <div class="section-header">
                    <h2>Recipe Types</h2>
                </div>
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
                                <td data-label="Name"><?php echo h($type->name); ?></td>
                                <td data-label="Recipes"><?php echo Recipe::count_by_type($type->id); ?></td>
                                <td data-label="Actions" class="actions">
                                    <a href="<?php echo url_for('/admin/categories/type/edit.php?id=' . h(u($type->id))); ?>" class="action edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo url_for('/admin/categories/type/delete.php?id=' . h(u($type->id))); ?>" class="action delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="add-new-button">
                    <a href="<?php echo url_for('/admin/categories/type/new.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Type
                    </a>
                </div>
            </section>

            <!-- Recipe Measurements Section -->
            <section class="metadata-section">
                <div class="section-header">
                    <h2>Recipe Measurements</h2>
                </div>
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
                                <td data-label="Name"><?php echo h($measurement->name); ?></td>
                                <td data-label="Actions" class="actions">
                                    <a href="<?php echo url_for('/admin/categories/measurement/edit.php?id=' . h(u($measurement->measurement_id))); ?>" class="action edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo url_for('/admin/categories/measurement/delete.php?id=' . h(u($measurement->measurement_id))); ?>" class="action delete" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="add-new-button">
                    <a href="<?php echo url_for('/admin/categories/measurement/new.php'); ?>" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Measurement
                    </a>
                </div>
            </section>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
