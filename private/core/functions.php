<?php

/**
 * Creates a URL-safe string from the given path
 * @param string $script_path The path to make URL-safe
 * @return string The URL-safe string
 */
function url_for($script_path) {
  // add the leading '/' if not present
  if($script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  return WWW_ROOT . $script_path;
}

/**
 * URL-encodes a string
 * @param string $string The string to encode
 * @return string The encoded string
 */
function u($string="") {
  return urlencode($string);
}

/**
 * Raw URL-encodes a string
 * @param string $string The string to encode
 * @return string The encoded string
 */
function raw_u($string="") {
  return rawurlencode($string);
}

/**
 * HTML escapes a string for safe output
 * @param string $string The string to escape
 * @return string The escaped string
 */
function h($string="") {
  return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirects to another URL
 * @param string $location The URL to redirect to
 * @return void
 */
function redirect_to($location) {
  header("Location: " . $location);
  exit;
}

/**
 * Checks if the current request is a POST request
 * @return bool True if request is POST
 */
function is_post_request() {
  return $_SERVER['REQUEST_METHOD'] == 'POST';
}

/**
 * Checks if the current request is a GET request
 * @return bool True if request is GET
 */
function is_get_request() {
  return $_SERVER['REQUEST_METHOD'] == 'GET';
}

/**
 * Polyfill for money_format() on Windows systems
 * 
 * PHP's money_format() function is not available on Windows. This function
 * provides a simple replacement that formats a number as US currency.
 * 
 * @param string $format   The format specification (unused, included for compatibility)
 * @param float  $number   The number to format
 * @return string         The formatted number with '$' prefix and 2 decimal places
 */
if(!function_exists('money_format')) {
  function money_format($format, $number) {
    return '$' . number_format($number, 2);
  }
}

/**
 * Gets the current page name from the URL
 * @return string The current page name
 */
function current_page() {
  $this_page = basename($_SERVER['SCRIPT_NAME']);
  return $this_page;
}

/**
 * Get all required CSS files for the current page
 * @return array Array of CSS file paths
 */
function get_css_files() {
    $css_files = [
        // Base styles (always loaded)
        '/assets/css/base.css',
        '/assets/css/layout.css',
        
        // Layout grids (always loaded)
        '/assets/css/layout/header-grid.css',
        '/assets/css/layout/footer-grid.css'
    ];
    
    // Add page-specific grid if it exists
    $current_page = current_page();
    $page_grid = "/assets/css/layout/{$current_page}-grid.css";
    if(file_exists(PUBLIC_PATH . $page_grid)) {
        $css_files[] = $page_grid;
    }
    
    return $css_files;
}

/**
 * Generate HTML for loading all required CSS files
 * @return string HTML string with CSS link tags
 */
function load_css() {
    $css_html = '';
    foreach(get_css_files() as $css_file) {
        $css_html .= '<link rel="stylesheet" href="' . url_for($css_file) . '">' . "\n";
    }
    return $css_html;
}

?>
