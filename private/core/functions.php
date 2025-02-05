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
 * Creates a URL-safe string from the given private path
 * @param string $script_path The path to make URL-safe
 * @return string The URL-safe string
 */
function private_url_for($script_path) {
  // add the leading '/' if not present
  if($script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  return PRIVATE_WWW_ROOT . $script_path;
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
 * @param string|null $string The string to escape
 * @return string The escaped string
 */
function h($string="") {
    if ($string === null) {
        return '';
    }
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
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
  $this_page = $_SERVER['SCRIPT_NAME'];
  $bits = explode('/', $this_page);
  $this_page = end($bits);
  return $this_page;
}

/**
 * Formats a recipe quantity as a fraction
 * @param float $value The quantity to format
 * @return string The formatted quantity
 */
function format_quantity($value) {
    // Convert common decimals to fractions
    $value = floatval($value);
    
    if ($value == 0.25) return '¼';
    if ($value == 0.5) return '½';
    if ($value == 0.75) return '¾';
    if ($value == 0.33) return '⅓';
    if ($value == 0.67) return '⅔';
    if ($value == 1.25) return '1¼';
    if ($value == 1.5) return '1½';
    if ($value == 1.75) return '1¾';
    
    // For whole numbers, return as is
    if (floor($value) == $value) {
        return (string)$value;
    }
    
    // For other decimals, round to 2 places
    return number_format($value, 2);
}

?>
