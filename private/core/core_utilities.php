<?php

/**
 * Creates a URL-safe string from the given path
 * @param string $script_path The path to make URL-safe
 * @return string The URL-safe string
 */
function url_for($script_path) {
  // Ensure script_path is a string
  if (!is_string($script_path)) {
    $script_path = (string)$script_path;
  }
  
  // add the leading '/' if not present
  if($script_path !== '' && isset($script_path[0]) && $script_path[0] != '/') {
    $script_path = "/" . $script_path;
  }
  
  // Environment-specific URL handling
  switch(ENVIRONMENT) {
    case 'production':
      // Use the WWW_ROOT constant defined in bluehost_config.php
      // Add a fallback value if WWW_ROOT is not defined
      $root = defined('WWW_ROOT') ? WWW_ROOT : 'https://flavorconnect.space';
      return $root . $script_path;
      
    case 'xampp':
      // Get the base URL from the server
      $base_url = isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST']) 
        ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] 
        : '';
      // Hardcode the project folder and public directory for XAMPP
      return $base_url . '/FlavorConnect/public' . $script_path;
      
    case 'docker':
    default:
      // Get the base URL from the server
      $base_url = isset($_SERVER['REQUEST_SCHEME']) && isset($_SERVER['HTTP_HOST']) 
        ? $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] 
        : '';
      // For Docker, the document root is already set to the public directory
      return $base_url . $script_path;
  }
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
 * Gets the raw decimal value for form inputs
 * @param float $value The quantity to format
 * @return float The raw decimal value
 */
function get_raw_quantity($value) {
    return number_format(floatval($value), 2);
}

/**
 * Formats a recipe quantity as a fraction
 * @param float $value The quantity to format
 * @param string $precision The precision level ('basic' for ¼, ½, ¾ or 'extended' for more fractions)
 * @return string The formatted quantity
 */
function format_quantity($value, $precision = 'basic') {
    if ($value == 0) return '0';
    
    $wholePart = floor($value);
    $decimal = $value - $wholePart;
    
    // Convert decimal to fraction
    $fraction = '';
    
    if ($precision === 'extended') {
        // Extended precision with more fraction options
        if ($decimal >= 0.9375) {
            $fraction = '';
            $wholePart += 1;
        } else if ($decimal >= 0.875) {
            $fraction = '⅞';
        } else if ($decimal >= 0.8125) {
            $fraction = '⅚';
        } else if ($decimal >= 0.75) {
            $fraction = '¾';
        } else if ($decimal >= 0.6875) {
            $fraction = '⅔';
        } else if ($decimal >= 0.625) {
            $fraction = '⅝';
        } else if ($decimal >= 0.5625) {
            $fraction = '⅗';
        } else if ($decimal >= 0.5) {
            $fraction = '½';
        } else if ($decimal >= 0.4375) {
            $fraction = '⅖';
        } else if ($decimal >= 0.375) {
            $fraction = '⅜';
        } else if ($decimal >= 0.3125) {
            $fraction = '⅓';
        } else if ($decimal >= 0.25) {
            $fraction = '¼';
        } else if ($decimal >= 0.1875) {
            $fraction = '⅕';
        } else if ($decimal >= 0.125) {
            $fraction = '⅛';
        } else if ($decimal >= 0.0625) {
            $fraction = '⅟16';
        }
    } else {
        // Basic precision with expanded fractions
        if ($decimal >= 0.9375) {
            $fraction = '';
            $wholePart += 1;
        } else if ($decimal >= 0.8125) {
            $fraction = '⅞'; // ⅞
        } else if ($decimal >= 0.7) {
            $fraction = '¾'; // ¾
        } else if ($decimal >= 0.58) {
            $fraction = '⅔'; // ⅔
        } else if ($decimal >= 0.45) {
            $fraction = '½'; // ½
        } else if ($decimal >= 0.375) {
            $fraction = '⅖'; // ⅖
        } else if ($decimal >= 0.29) {
            $fraction = '⅓'; // ⅓
        } else if ($decimal >= 0.225) {
            $fraction = '¼'; // ¼
        } else if ($decimal >= 0.175) {
            $fraction = '⅕'; // ⅕
        } else if ($decimal >= 0.0625) {
            $fraction = '⅛'; // ⅛
        }
    }
    
    // Format the final string
    if ($wholePart == 0) {
        return $fraction ?: '0';
    } else if ($fraction) {
        return $wholePart . ' ' . $fraction;
    } else {
        return (string)$wholePart;
    }
}

/**
 * Generates a smart back link URL and suggested text
 * 
 * This function determines the most appropriate back link based on:
 * 1. The 'ref_page' parameter in the query string (highest priority)
 * 2. The HTTP_REFERER if available
 * 3. A default fallback URL
 * 
 * @param string $default_url The default URL to use if no referer is available
 * @param array $allowed_domains Array of allowed domains for referer (empty allows any)
 * @param string $default_text Default text to use for the back link
 * @return array Associative array with 'url' and 'text' keys
 */
function get_back_link($default_url = '/index.php', $allowed_domains = [], $default_text = 'Back') {
    // Debug information for troubleshooting
    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log('GET_BACK_LINK CALLED');
        error_log('DEFAULT_URL: ' . $default_url);
        error_log('GET PARAMS: ' . print_r($_GET, true));
    }
    /**
     * Navigation Context Coordination
     * 
     * This system uses a dual approach to maintain navigation context:
     * 
     * 1. Server-side (PHP):
     *    - Uses $_GET['ref_page'] for back link generation
     *    - Stores recipe context in $_GET['id'] for recipe pages
     *    - Stores category context in $_GET['category_id'] and $_GET['category_type']
     *    - Preserves action context in $_GET['action_type']
     * 
     * 2. Client-side (JavaScript):
     *    - Uses sessionStorage.fromRecipeId to track recipe context
     *    - Enhances links with ref_page and recipe_id parameters
     * 
     * This coordination ensures consistent navigation with or without JavaScript.
     */
    
    // Initialize result array with defaults
    $result = [
        'url' => url_for($default_url),
        'text' => $default_text
    ];
    
    // Define a comprehensive mapping of paths to titles
    $path_to_title_map = [
        // Main pages
        '/index.php' => 'Home',
        '/about.php' => 'About Us',
        
        // Recipe pages
        '/recipes/index.php' => 'Recipes',
        '/recipes/show.php' => 'Recipe',
        '/recipes/new.php' => 'Create Recipe',
        '/recipes/edit.php' => 'Edit Recipe',
        '/recipes/delete.php' => 'Delete Recipe',
        
        // User pages
        '/users/profile.php' => 'Profile',
        '/users/favorites.php' => 'Favorites',
        
        // Admin pages
        '/admin/index.php' => 'Admin Dashboard',
        
        // Admin - User Management
        '/admin/users/index.php' => 'User Management',
        '/admin/users/new.php' => 'Create User',
        '/admin/users/edit.php' => 'Edit User',
        '/admin/users/delete.php' => 'Delete User',
        
        // Admin - Categories
        '/admin/categories/index.php' => 'Recipe Metadata',
        
        // Admin - Diet Categories
        '/admin/categories/diet/new.php' => 'New Diet Category',
        '/admin/categories/diet/edit.php' => 'Edit Diet Category',
        '/admin/categories/diet/delete.php' => 'Delete Diet Category',
        
        // Admin - Style Categories
        '/admin/categories/style/new.php' => 'New Style Category',
        '/admin/categories/style/edit.php' => 'Edit Style Category',
        '/admin/categories/style/delete.php' => 'Delete Style Category',
        
        // Admin - Type Categories
        '/admin/categories/type/new.php' => 'New Type Category',
        '/admin/categories/type/edit.php' => 'Edit Type Category',
        '/admin/categories/type/delete.php' => 'Delete Type Category',
        
        // Admin - Measurement Categories
        '/admin/categories/measurement/new.php' => 'New Measurement Category',
        '/admin/categories/measurement/edit.php' => 'Edit Measurement Category',
        '/admin/categories/measurement/delete.php' => 'Delete Measurement Category',

        // Auth pages
        '/auth/login.php' => 'Login',
        '/auth/register.php' => 'Register'
    ];
    
    // Check if the default URL is in our path-to-title map and set appropriate text
    if (isset($path_to_title_map[$default_url])) {
        $result['text'] = 'Back to ' . $path_to_title_map[$default_url];
    }
    
    // First check for ref_page parameter in query string (highest priority)
    $ref_page = $_GET['ref_page'] ?? '';
    if ($ref_page) {
        // Normalize the ref_page to ensure it starts with a slash and doesn't contain environment-specific prefixes
        // This is critical for cross-environment compatibility
        
        // Remove any environment-specific prefixes
        $ref_page = normalize_path($ref_page);
        
        // Now ensure it starts with a slash
        if (strpos($ref_page, '/') !== 0) {
            $ref_page = '/' . $ref_page;
        }
        
        // Handle the case where ref_page contains a query string (e.g., /recipes/show.php?id=123)
        $query_pos = strpos($ref_page, '?');
        if ($query_pos !== false) {
            // Extract the path and query parts
            $path = substr($ref_page, 0, $query_pos);
            $query = substr($ref_page, $query_pos + 1);
            
            // Parse the query string into an associative array
            parse_str($query, $query_params);
            
            // Build the URL with the path and query parameters
            $result['url'] = url_for($path);
            if (!empty($query_params)) {
                $result['url'] .= '?' . http_build_query($query_params);
            }
            
            // Set the back link text based on the path-to-title map
            if (isset($path_to_title_map[$path])) {
                $result['text'] = 'Back to ' . $path_to_title_map[$path];
            }
        } else {
            // It's a simple path without query parameters
            $result['url'] = url_for($ref_page);
            
            // Set the back link text based on the path-to-title map
            if (isset($path_to_title_map[$ref_page])) {
                $result['text'] = 'Back to ' . $path_to_title_map[$ref_page];
            }
        }
        
        // Check if we have recipe_id parameter to handle
        $recipe_id = null;
        
        // Check if recipe_id is in the current request
        if (isset($_GET['recipe_id']) && is_numeric($_GET['recipe_id'])) {
            $recipe_id = $_GET['recipe_id'];
        }
        
        // If we found a recipe_id, determine the correct page to link back to
        if ($recipe_id) {
            // Default to show page
            $path = '/recipes/show.php';
            
            // Check if the ref_page indicates we should go back to edit or delete page
            if (strpos($ref_page, '/recipes/edit.php') !== false) {
                $path = '/recipes/edit.php';
            } elseif (strpos($ref_page, '/recipes/delete.php') !== false) {
                $path = '/recipes/delete.php';
            }
            
            $result['url'] = url_for($path . '?id=' . $recipe_id);
            $result['text'] = 'Back to ' . $path_to_title_map[$path];
        }
        
        // Special handling for profile page
        if (strpos($ref_page, '/users/profile.php') !== false) {
            $result['url'] = url_for('/users/profile.php');
            $result['text'] = 'Back to Profile';
        }
        
        // Check if we have user_id parameter to handle
        $user_id = null;
        
        // Check if user_id is in the current request
        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
        }
        
        // If we found a user_id, determine the correct page to link back to
        if ($user_id) {
            // Default to edit page
            $path = '/admin/users/edit.php';
            
            // Check if the ref_page indicates we should go back to delete page
            if (strpos($ref_page, '/admin/users/delete.php') !== false) {
                $path = '/admin/users/delete.php';
            }
            
            $result['url'] = url_for($path . '?user_id=' . $user_id);
            $result['text'] = 'Back to ' . ($path_to_title_map[$path] ?? 'User Management');
        }
        
        // Check if we have category parameters to handle
        if (isset($_GET['category_id']) && is_numeric($_GET['category_id']) && isset($_GET['category_type'])) {
            $category_type = $_GET['category_type'];
            
            // Only process valid category types
            if (in_array($category_type, ['diet', 'style', 'type', 'measurement'])) {
                // Determine if we're coming from an edit or delete page
                $action_type = '';
                if (isset($_GET['action_type']) && in_array($_GET['action_type'], ['edit', 'delete'])) {
                    $action_type = $_GET['action_type'];
                }
                
                // If we have a specific action type, return to that page
                if (!empty($action_type)) {
                    $result['url'] = url_for('/admin/categories/' . $category_type . '/' . $action_type . '.php?id=' . $_GET['category_id']);
                    $result['text'] = 'Back to ' . ucfirst($action_type) . ' ' . ucfirst($category_type) . ' Category';
                } else {
                    // Default back to categories index with appropriate text
                    $result['text'] = 'Back to ' . ucfirst($category_type) . ' Categories';
                }
            }
        }
        
        // Check if we have gallery parameters to preserve pagination and filters
        if (isset($_GET['gallery_params'])) {
            // Make sure we're not double-decoding
            $gallery_params = $_GET['gallery_params'];
            // Check if it's already decoded
            if (strpos($gallery_params, '%') !== false) {
                $gallery_params = urldecode($gallery_params);
            }
            
            // Determine the correct page to return to based on ref_page
            if (strpos($ref_page, '/recipes/index.php') !== false) {
                $result['url'] = url_for('/recipes/index.php?' . $gallery_params);
            } elseif (strpos($ref_page, '/users/favorites.php') !== false) {
                $result['url'] = url_for('/users/favorites.php?' . $gallery_params);
                $result['text'] = 'Back to Favorites';
            }
        }
        
        // Special handling for favorites page (when no gallery_params)
        if (strpos($ref_page, '/users/favorites.php') !== false && !isset($_GET['gallery_params'])) {
            $result['url'] = url_for('/users/favorites.php');
            $result['text'] = 'Back to Favorites';
            
            // Check if the ref_page contains a page parameter
            $page_param = null;
            if (strpos($ref_page, 'page=') !== false) {
                // Extract the page parameter from ref_page
                preg_match('/page=(\d+)/', $ref_page, $matches);
                if (isset($matches[1]) && is_numeric($matches[1])) {
                    $page_param = $matches[1];
                }
            }
            
            // Only preserve pagination context when coming from a recipe show page
            // This ensures we only add the page parameter when using the back button from a recipe
            $coming_from_recipe_show = strpos($_SERVER['PHP_SELF'], '/recipes/show.php') !== false;
            
            if ($coming_from_recipe_show && $page_param) {
                $result['url'] .= '?page=' . $page_param;
            }
            
            return $result;
        }
        
        // Handle pagination for other gallery pages
        if (isset($_GET['page']) && is_numeric($_GET['page'])) {
            // Only add page parameter if the URL doesn't already have it
            $url_has_page = false;
            
            // Check if the URL already has a query string
            if (strpos($result['url'], '?') !== false) {
                // Extract existing query parameters
                $url_parts = parse_url($result['url']);
                if (isset($url_parts['query'])) {
                    parse_str($url_parts['query'], $query_params);
                    // Check if page parameter already exists
                    $url_has_page = isset($query_params['page']);
                }
                
                // Add page parameter if it doesn't exist
                if (!$url_has_page) {
                    $result['url'] .= '&page=' . $_GET['page'];
                }
            } else {
                // No query string, add page parameter
                $result['url'] .= '?page=' . $_GET['page'];
            }
        }
        
        // Set appropriate back text based on the back link
        // First, try to find an exact match in the path-to-title mapping
        $found_exact_match = false;
        foreach ($path_to_title_map as $path => $title) {
            if ($ref_page === $path) {
                $result['text'] = 'Back to ' . $title;
                $found_exact_match = true;
                break;
            }
        }
        
        // If no exact match, use pattern matching with our helper function
        if (!$found_exact_match) {
            $back_text = get_back_text_from_path($ref_page);
            if ($back_text !== 'Back') { // Only update if we got a meaningful result
                $result['text'] = $back_text;
            }
        }
        return $result;
    }
    
    // We've standardized on ref_page, so we don't need to check for ref parameter
    
    // Then check HTTP_REFERER
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if ($referer) {
        // Parse the referer URL
        $referer_parts = parse_url($referer);
        $host = $referer_parts['host'] ?? '';
        $path = $referer_parts['path'] ?? '';
        
        // If allowed_domains is empty, allow any domain
        // Otherwise, check if the referer's domain is in the allowed list
        $is_allowed = empty($allowed_domains) || in_array($host, $allowed_domains);
        
        if ($is_allowed) {
            // Don't use referer if it's the same as current page to avoid loops
            $current_path = $_SERVER['REQUEST_URI'] ?? '';
            if ($path !== $current_path) {
                $result['url'] = $referer;
                
                // Extract the script name from the path for more accurate matching
                $script_name = normalize_path($path);
                
                // Clean up the script name by removing query parameters
                $script_name = strtok($script_name, '?');
                
                // Try to find an exact match in our path mapping
                if (isset($path_to_title_map[$script_name])) {
                    $result['text'] = 'Back to ' . $path_to_title_map[$script_name];
                    return $result;
                }
                
                // If no exact match, use pattern matching with our helper function
                $back_text = get_back_text_from_path($path);
                if ($back_text !== 'Back') { // Only update if we got a meaningful result
                    $result['text'] = $back_text;
                }
                
                return $result;
            }
        }
    }
    
    // Check if we're coming from a recipe page (using session)
    if (isset($_SESSION['from_recipe_id']) && is_numeric($_SESSION['from_recipe_id'])) {
        $result['url'] = url_for('/recipes/show.php?id=' . $_SESSION['from_recipe_id']);
        $result['text'] = 'Back to Recipe';
        return $result;
    }
    
    // Return the default if no other options are available
    return $result;
}

/**
 * Adds context parameters to a reference parameter string
 * 
 * @param string $ref_param The current reference parameter string
 * @param array $params Associative array of parameters to add
 * @return string The updated reference parameter string
 */
function add_context_to_ref_param($ref_param, $params) {
    foreach ($params as $key => $value) {
        if (!empty($value)) {
            if (!empty($ref_param)) {
                $ref_param .= '&';
            } else {
                $ref_param = '?';
            }
            $ref_param .= $key . '=' . $value;
        }
    }
    
    return $ref_param;
}

/**
 * Extracts category type and action from a path
 * 
 * @param string $path The path to analyze
 * @return array Associative array with 'category_type' and 'action_type' keys
 */
function extract_category_info_from_path($path) {
    $result = [
        'category_type' => '',
        'action_type' => ''
    ];
    
    $path_parts = explode('/', $path);
    
    foreach ($path_parts as $index => $part) {
        if ($part === 'categories' && isset($path_parts[$index + 1])) {
            $result['category_type'] = $path_parts[$index + 1];
            
            if (isset($path_parts[$index + 2])) {
                $file_name = pathinfo($path_parts[$index + 2], PATHINFO_FILENAME);
                if (in_array($file_name, ['edit', 'delete', 'new'])) {
                    $result['action_type'] = $file_name;
                }
            }
            break;
        }
    }
        
    return $result;
}

/**
 * Determines the appropriate back link text based on a path pattern
 * 
 * @param string $path The path to analyze
 * @return string The appropriate back link text
 */
function get_back_text_from_path($path) {
    // Check for specific category pages first (most specific patterns first)
    // Style category pages
    if (strpos($path, '/admin/categories/style/new.php') !== false) {
        return 'Back to New Style Category';
    } elseif (strpos($path, '/admin/categories/style/edit.php') !== false) {
        return 'Back to Edit Style Category';
    } elseif (strpos($path, '/admin/categories/style/delete.php') !== false) {
        return 'Back to Delete Style Category';
    } 
    // Diet category pages
    elseif (strpos($path, '/admin/categories/diet/new.php') !== false) {
        return 'Back to New Diet Category';
    } elseif (strpos($path, '/admin/categories/diet/edit.php') !== false) {
        return 'Back to Edit Diet Category';
    } elseif (strpos($path, '/admin/categories/diet/delete.php') !== false) {
        return 'Back to Delete Diet Category';
    } 
    // Type category pages
    elseif (strpos($path, '/admin/categories/type/new.php') !== false) {
        return 'Back to New Type Category';
    } elseif (strpos($path, '/admin/categories/type/edit.php') !== false) {
        return 'Back to Edit Type Category';
    } elseif (strpos($path, '/admin/categories/type/delete.php') !== false) {
        return 'Back to Delete Type Category';
    } 
    // Measurement category pages
    elseif (strpos($path, '/admin/categories/measurement/new.php') !== false) {
        return 'Back to New Measurement Category';
    } elseif (strpos($path, '/admin/categories/measurement/edit.php') !== false) {
        return 'Back to Edit Measurement Category';
    } elseif (strpos($path, '/admin/categories/measurement/delete.php') !== false) {
        return 'Back to Delete Measurement Category';
    }
    // All category types are displayed on the same page (admin/categories/index.php)
    elseif (strpos($path, '/admin/categories/style') !== false ||
           strpos($path, '/admin/categories/diet') !== false ||
           strpos($path, '/admin/categories/type') !== false ||
           strpos($path, '/admin/categories/measurement') !== false) {
        return 'Back to Recipe Metadata';
    } 
    // User management pages
    elseif (strpos($path, '/admin/users/edit.php') !== false) {
        return 'Back to Edit User';
    } elseif (strpos($path, '/admin/users/delete.php') !== false) {
        return 'Back to Delete User';
    } elseif (strpos($path, '/admin/users/new.php') !== false) {
        return 'Back to New User';
    } elseif (strpos($path, '/admin/users') !== false) {
        return 'Back to User Management';
    } elseif (strpos($path, '/admin/categories') !== false) {
        return 'Back to Recipe Metadata';
    } elseif (strpos($path, '/admin/') !== false) {
        return 'Back to Admin Dashboard';
    }
    // Recipe section pattern matching
    elseif (strpos($path, '/recipes/index.php') !== false) {
        return 'Back to Recipes';
    } elseif (strpos($path, '/recipes/show.php') !== false) {
        return 'Back to Recipe';
    } elseif (strpos($path, '/recipes/new.php') !== false) {
        return 'Back to Create Recipe';
    } elseif (strpos($path, '/recipes/edit.php') !== false) {
        return 'Back to Edit Recipe';
    } elseif (strpos($path, '/recipes/delete.php') !== false) {
        return 'Back to Delete Recipe';
    }
    // User section pattern matching
    elseif (strpos($path, '/users/favorites.php') !== false) {
        return 'Back to Favorites';
    } elseif (strpos($path, '/users/profile.php') !== false) {
        return 'Back to Profile';
    }
    // Other pages pattern matching
    elseif (strpos($path, '/index.php') !== false || $path == '/') {
        return 'Back to Home';
    } elseif (strpos($path, '/about.php') !== false) {
        return 'Back to About Us';
    }
    
    // Default
    return 'Back';
}

/**
 * Generates a reference parameter for consistent back navigation
 * 
 * This function determines the current section of the site and returns
 * an appropriate reference parameter that can be appended to URLs.
 * 
 * @param string $type The type of reference parameter to generate ('ref_page' or 'ref')
 * @return string The reference parameter (e.g., '?ref_page=/recipes/index.php') or empty string if not applicable
 */
function get_ref_parameter($type = 'ref_page') {
    // Determine the current section based on the URL
    $current_path = $_SERVER['PHP_SELF'] ?? '';
    $ref_param = '';
    
    /**
     * Navigation Context Coordination
     * 
     * This system uses a dual approach to maintain navigation context:
     * 
     * 1. Server-side (PHP):
     *    - Uses $_GET['ref_page'] for back link generation
     *    - Stores recipe context in $_GET['id'] for recipe pages
     *    - Stores category context in $_GET['category_id'] and $_GET['category_type']
     *    - Preserves action context in $_GET['action_type']
     * 
     * 2. Client-side (JavaScript):
     *    - Uses sessionStorage.fromRecipeId to track recipe context
     *    - Enhances links with ref_page and recipe_id parameters
     * 
     * This coordination ensures consistent navigation with or without JavaScript.
     */
    
    // Set the base reference parameter based on type
    $ref_param = '?ref_page=' . $current_path;
    
    // Collect context parameters
    $context_params = [];
    
    // Add recipe ID if we're on a recipe page (show, edit, or delete)
    if ((strpos($current_path, '/recipes/show.php') !== false || 
         strpos($current_path, '/recipes/edit.php') !== false || 
         strpos($current_path, '/recipes/delete.php') !== false) && 
        isset($_GET['id'])) {
        // Add recipe_id as a context parameter
        $context_params['recipe_id'] = $_GET['id'];
    }
    
    // Add user ID if we're on a user edit or delete page
    if ((strpos($current_path, '/admin/users/edit.php') !== false || 
         strpos($current_path, '/admin/users/delete.php') !== false) && 
        isset($_GET['user_id'])) {
        // Add user_id as a context parameter
        $context_params['user_id'] = $_GET['user_id'];
    }
    
    // Add category context if we're on an admin category page
    if (strpos($current_path, '/admin/categories/') !== false && isset($_GET['id'])) {
        // Extract category information using our helper function
        $category_info = extract_category_info_from_path($current_path);
        $category_type = $category_info['category_type'];
        $action_type = $category_info['action_type'];
        
        // Only add category parameters if we found a valid category type
        if ($category_type && in_array($category_type, ['diet', 'style', 'type', 'measurement'])) {
            $context_params['category_id'] = $_GET['id'];
            $context_params['category_type'] = $category_type;
            
            // Add action type if available
            if (!empty($action_type)) {
                $context_params['action_type'] = $action_type;
            }
        }
    }
    
    // Add all context parameters to the ref_param
    foreach ($context_params as $key => $value) {
        $ref_param .= '&' . $key . '=' . $value;
    }
    
    return $ref_param;
}

/**
 * Normalizes a path by removing environment-specific prefixes
 * 
 * This function ensures paths are consistent across all environments
 * by removing prefixes like '/FlavorConnect/public'
 * 
 * @param string $path The path to normalize
 * @return string The normalized path
 */
function normalize_path($path) {
    // Remove any environment-specific prefixes
    $prefixes = [
        '/FlavorConnect/public',
        '/flavorconnect/public',
        '/public'
    ];
    
    foreach ($prefixes as $prefix) {
        if (strpos($path, $prefix) === 0) {
            // Remove the prefix from the beginning of the path
            $path = substr($path, strlen($prefix));
            break;
        } else if (strpos($path, $prefix) !== false) {
            // If the prefix is somewhere in the path (not at the beginning)
            $path = str_replace($prefix, '', $path);
            break;
        }
    }
    
    // Ensure the path starts with a slash
    if (empty($path) || $path[0] !== '/') {
        $path = '/' . $path;
    }
    
    return $path;
}

/**
 * Generates a unified navigation component with back link and breadcrumbs
 * 
 * @param string $default_back_url The default URL to use if no referer is available
 * @param array $breadcrumbs Array of breadcrumb items, each with 'url' and 'label' keys
 * @param string $back_text Custom text for the back link (optional)
 * @param array $allowed_domains Array of allowed domains for referer (empty allows any)
 * @return string HTML for the unified navigation component with SEO structured data
 */
function unified_navigation($default_back_url = '/index.php', $breadcrumbs = [], $back_text = 'Back', $allowed_domains = []) {
    $back_link_data = get_back_link($default_back_url, $allowed_domains, $back_text);
    
    // Use the provided back_text if it's not the default 'Back', otherwise use the suggested text
    $display_text = ($back_text !== 'Back') ? $back_text : $back_link_data['text'];
    
    $html = '<div class="unified-navigation">';
    
    // Add back link
    // Ensure URL is a string and properly formatted
    $back_url = is_array($back_link_data['url']) ? json_encode($back_link_data['url']) : $back_link_data['url'];
    
    // Make sure the URL is properly formatted with url_for if it's a relative path
    if ($back_url && substr($back_url, 0, 1) === '/' && substr($back_url, 0, 4) !== 'http') {
        $back_url = url_for($back_url);
    }
    
    // Add data attributes to help JavaScript identify the back link type
    $data_attrs = '';
    if (isset($_GET['ref_page'])) {
        $data_attrs .= ' data-ref-page="' . h($_GET['ref_page']) . '"';
    }
    if (isset($_GET['recipe_id'])) {
        $data_attrs .= ' data-recipe-id="' . h($_GET['recipe_id']) . '"';
    }
    
    $html .= '<a href="' . h($back_url) . '" class="back-link"' . $data_attrs . '>';
    $html .= '<i class="fas fa-arrow-left"></i> ' . h($display_text);
    $html .= '</a>';
    
    // Add breadcrumbs if provided
    if (!empty($breadcrumbs)) {
        // Add BreadcrumbList structured data for SEO
        $html .= '<script type="application/ld+json">';
        $html .= '{';
        $html .= '"@context": "https://schema.org",';
        $html .= '"@type": "BreadcrumbList",';
        $html .= '"itemListElement": [';
        
        foreach ($breadcrumbs as $index => $crumb) {
            $html .= '{';
            $html .= '"@type": "ListItem",';
            $html .= '"position": ' . ($index + 1) . ',';
            $html .= '"name": "' . h($crumb['label']) . '"';
            if (isset($crumb['url'])) {
                $html .= ',"item": "' . url_for(h($crumb['url'])) . '"';
            }
            $html .= '}';
            if ($index < count($breadcrumbs) - 1) {
                $html .= ',';
            }
        }
        
        $html .= ']';
        $html .= '}';
        $html .= '</script>';
        
        // Visual breadcrumbs with appropriate ARIA attributes
        $html .= '<nav aria-label="Breadcrumb">';
        $html .= '<ol class="breadcrumbs" itemscope itemtype="https://schema.org/BreadcrumbList">';
        
        $count = count($breadcrumbs);
        foreach ($breadcrumbs as $index => $crumb) {
            $html .= '<li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">';
            
            if (isset($crumb['url']) && $index < $count - 1) {
                // Not the last item, make it a link
                $html .= '<a itemprop="item" href="' . url_for(h($crumb['url'])) . '" class="breadcrumb-item">';
                $html .= '<span itemprop="name">' . h($crumb['label']) . '</span>';
                $html .= '</a>';
            } else {
                // Last item or no URL, just text
                $html .= '<span itemprop="name" class="breadcrumb-item breadcrumb-active" aria-current="page">' . h($crumb['label']) . '</span>';
            }
            
            $html .= '<meta itemprop="position" content="' . ($index + 1) . '" />';
            
            // Add separator if not the last item
            if ($index < $count - 1) {
                $html .= '<span class="breadcrumb-separator" aria-hidden="true">/</span>';
            }
            
            $html .= '</li>';
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Include the recipe card component with environment-specific path handling
 * 
 * @param string $ref Reference page (e.g., 'home', 'gallery', 'favorites')
 * @param array $params Additional parameters to pass to the recipe card
 * @return void
 */
function include_recipe_card($ref, $params = []) {
    // Make sure $ref is passed through to the recipe card template
    // Do not override if already in $params
    if (!isset($params['ref'])) {
        $params['ref'] = $ref;
    }
    
    // Extract any additional parameters passed to the function
    extract($params);
    
    // Use absolute path in production, relative path in development
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
        include(PUBLIC_PATH . '/recipes/recipe-card.php');
    } else {
        // In development, use relative path based on the current directory
        $current_dir = basename(dirname($_SERVER['SCRIPT_FILENAME']));
        
        if ($current_dir === 'recipes') {
            include('recipe-card.php');
        } else if ($current_dir === 'users') {
            include('../recipes/recipe-card.php');
        } else {
            // For home page or other locations
            include('recipes/recipe-card.php');
        }
    }
}

?>
