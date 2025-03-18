<?php
require_once('../private/initialize.php');
$page_title = '500 - Server Error';
$page_style = 'error'; 

// Use member header if user is logged in, otherwise use public header
if(is_logged_in()) {
    include(SHARED_PATH . '/member_header.php');
} else {
    include(SHARED_PATH . '/public_header.php');
}
?>

<main class="error-500">
    <div class="container">
        <div class="error-content">
            <h1>500</h1>
            <h2>Oops! Something Went Wrong</h2>
            <p>Looks like our servers are experiencing some technical difficulties. Our team has been notified and is working on a fix.</p>
            <div class="error-actions">
                <a href="<?php echo url_for('/'); ?>" class="btn btn-primary">
                    <i class="fas fa-home"></i> Return Home
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
            </div>
        </div>
    </div>
</main>

<?php include(SHARED_PATH . '/footer.php'); ?>
