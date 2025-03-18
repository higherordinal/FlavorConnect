<?php
// Simple script to check PHP upload limits and configuration

// Set page title
$page_title = 'PHP Configuration Check';

// Include header if available, otherwise output basic HTML
if (file_exists('../private/core/initialize.php')) {
    require_once('../private/core/initialize.php');
    include(SHARED_PATH . '/public_header.php');
} else {
    echo '<!DOCTYPE html><html><head><title>PHP Configuration Check</title>';
    echo '<style>body{font-family:Arial,sans-serif;line-height:1.6;margin:20px;} 
    table{border-collapse:collapse;width:100%;max-width:800px;margin:20px 0;} 
    th,td{border:1px solid #ddd;padding:8px;text-align:left;} 
    th{background-color:#f2f2f2;} 
    .container{max-width:1000px;margin:0 auto;padding:20px;}</style>';
    echo '</head><body><div class="container">';
}
?>

<div class="container mt-5">
    <h1>PHP Configuration Check</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>PHP Version and Environment</h2>
        </div>
        <div class="card-body">
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
            <p><strong>Server Name:</strong> <?php echo $_SERVER['SERVER_NAME'] ?? 'Unknown'; ?></p>
            <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></p>
            <p><strong>Running in Docker:</strong> <?php echo (file_exists('/.dockerenv') || getenv('DOCKER_CONTAINER')) ? 'Yes' : 'Unknown'; ?></p>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>PHP Upload Settings</h2>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                        <th>Bytes</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>upload_max_filesize</td>
                        <td><?php echo ini_get('upload_max_filesize'); ?></td>
                        <td><?php echo return_bytes(ini_get('upload_max_filesize')); ?></td>
                        <td>Maximum size of an uploaded file</td>
                    </tr>
                    <tr>
                        <td>post_max_size</td>
                        <td><?php echo ini_get('post_max_size'); ?></td>
                        <td><?php echo return_bytes(ini_get('post_max_size')); ?></td>
                        <td>Maximum size of POST data that PHP will accept</td>
                    </tr>
                    <tr>
                        <td>memory_limit</td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                        <td><?php echo return_bytes(ini_get('memory_limit')); ?></td>
                        <td>Maximum amount of memory a script may consume</td>
                    </tr>
                    <tr>
                        <td>max_execution_time</td>
                        <td><?php echo ini_get('max_execution_time'); ?> seconds</td>
                        <td>N/A</td>
                        <td>Maximum time in seconds a script is allowed to run</td>
                    </tr>
                    <tr>
                        <td>max_input_time</td>
                        <td><?php echo ini_get('max_input_time'); ?> seconds</td>
                        <td>N/A</td>
                        <td>Maximum time in seconds a script is allowed to parse input data</td>
                    </tr>
                    <tr>
                        <td>file_uploads</td>
                        <td><?php echo ini_get('file_uploads') ? 'Enabled' : 'Disabled'; ?></td>
                        <td>N/A</td>
                        <td>Whether or not to allow HTTP file uploads</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header">
            <h2>Test File Upload</h2>
        </div>
        <div class="card-body">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="test_file">Select a file to upload:</label>
                    <input type="file" class="form-control-file" id="test_file" name="test_file">
                    <small class="form-text text-muted">This is just a test - the file won't be saved.</small>
                </div>
                <button type="submit" class="btn btn-primary">Test Upload</button>
            </form>
            
            <?php
            // Check if a file was uploaded
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
                echo '<div class="mt-4">';
                echo '<h3>Upload Test Results</h3>';
                
                $file = $_FILES['test_file'];
                
                echo '<table class="table table-bordered">';
                echo '<tr><th>Property</th><th>Value</th></tr>';
                echo '<tr><td>Name</td><td>' . htmlspecialchars($file['name']) . '</td></tr>';
                echo '<tr><td>Type</td><td>' . htmlspecialchars($file['type']) . '</td></tr>';
                echo '<tr><td>Size</td><td>' . number_format($file['size']) . ' bytes (' . number_format($file['size'] / (1024 * 1024), 2) . ' MB)</td></tr>';
                echo '<tr><td>Error</td><td>';
                
                switch ($file['error']) {
                    case UPLOAD_ERR_OK:
                        echo '<span class="text-success">No error, upload successful!</span>';
                        break;
                    case UPLOAD_ERR_INI_SIZE:
                        echo '<span class="text-danger">The uploaded file exceeds the upload_max_filesize directive in php.ini</span>';
                        break;
                    case UPLOAD_ERR_FORM_SIZE:
                        echo '<span class="text-danger">The uploaded file exceeds the MAX_FILE_SIZE directive in the HTML form</span>';
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        echo '<span class="text-danger">The uploaded file was only partially uploaded</span>';
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        echo '<span class="text-danger">No file was uploaded</span>';
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        echo '<span class="text-danger">Missing a temporary folder</span>';
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        echo '<span class="text-danger">Failed to write file to disk</span>';
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        echo '<span class="text-danger">A PHP extension stopped the file upload</span>';
                        break;
                    default:
                        echo '<span class="text-danger">Unknown error code: ' . $file['error'] . '</span>';
                }
                
                echo '</td></tr>';
                echo '</table>';
                
                echo '</div>';
            }
            ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h2>PHP Configuration (phpinfo)</h2>
        </div>
        <div class="card-body">
            <p>Below is a subset of the PHP configuration information:</p>
            
            <?php
            // Capture phpinfo output
            ob_start();
            phpinfo(INFO_CONFIGURATION);
            $phpinfo = ob_get_clean();
            
            // Extract the body content
            $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
            
            // Output the content
            echo $phpinfo;
            ?>
        </div>
    </div>
</div>

<?php
// Function to convert shorthand byte values to bytes
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    
    return $val;
}

// Include footer if available
if (isset($SHARED_PATH)) {
    include(SHARED_PATH . '/public_footer.php');
} else {
    echo '</div></body></html>';
}
?>
