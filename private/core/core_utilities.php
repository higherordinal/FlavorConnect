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
 * 1. The 'ref' parameter in the query string (highest priority)
 * 2. The HTTP_REFERER if available
 * 3. A default fallback URL
 * 
 * @param string $default_url The default URL to use if no referer is available
 * @param array $allowed_domains Array of allowed domains for referer (empty allows any)
 * @param string $default_text Default text to use for the back link
 * @return array Associative array with 'url' and 'text' keys
 */
function get_back_link($default_url = '/index.php', $allowed_domains = [], $default_text = 'Back') {
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
    if ($ref_page && strpos($ref_page, '/') === 0) {
        // It's a valid internal URL, use it as the back link
        $result['url'] = url_for($ref_page);
        
        // Check if we have a recipe_id parameter to append
        if (isset($_GET['recipe_id']) && is_numeric($_GET['recipe_id'])) {
            // If the ref_page is a recipe show page, append the recipe_id
            if (strpos($ref_page, '/recipes/show.php') !== false) {
                $result['url'] = url_for('/recipes/show.php?id=' . $_GET['recipe_id']);
            }
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
        if (isset($_GET['gallery_params']) && strpos($ref_page, '/recipes/index.php') !== false) {
            // Make sure we're not double-decoding
            $gallery_params = $_GET['gallery_params'];
            // Check if it's already decoded
            if (strpos($gallery_params, '%') !== false) {
                $gallery_params = urldecode($gallery_params);
            }
            $result['url'] = url_for('/recipes/index.php?' . $gallery_params);
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
    
    // Check for ref parameter in query string (second priority)
    $ref = $_GET['ref'] ?? '';
    if ($ref) {
        // Map of ref values to paths and titles
        $ref_map = [
            'home' => ['/index.php', 'Home'],
            'recipes' => ['/recipes/index.php', 'Recipes'],
            'profile' => ['/users/profile.php', 'Profile'],
            'favorites' => ['/users/favorites.php', 'Favorites'],
            'gallery' => ['/recipes/index.php', 'Recipes'],
            'about' => ['/about.php', 'About Us'],
            'admin' => ['/admin/index.php', 'Admin Dashboard'],
            'contact' => ['/contact.php', 'Contact Us'],
            'settings' => ['/settings.php', 'Settings']
        ];
        
        // Check if the ref value is in our map
        if (isset($ref_map[$ref])) {
            $ref_info = $ref_map[$ref];
            $result['url'] = url_for($ref_info[0]);
            $result['text'] = 'Back to ' . $ref_info[1];
            return $result;
        }
    }
    
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
                $script_name = '';
                
                // Handle both development and production paths
                if (strpos($path, '/FlavorConnect/public') !== false) {
                    // Development path
                    $script_name = str_replace('/FlavorConnect/public', '', $path);
                } else {
                    // Production path
                    $script_name = $path;
                }
                
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
    
    // Try to determine a better text based on the current path
    $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Extract the script name from the path for more accurate matching
    $script_name = '';
    
    // Handle both development and production paths
    if (strpos($current_path, '/FlavorConnect/public') !== false) {
        // Development path
        $script_name = str_replace('/FlavorConnect/public', '', $current_path);
    } else {
        // Production path
        $script_name = $current_path;
    }
    
    // Clean up the script name by removing query parameters
    $script_name = strtok($script_name, '?');
    
    // Try to find an exact match in our path mapping
    if (isset($path_to_title_map[$script_name])) {
        $result['text'] = 'Back to ' . $path_to_title_map[$script_name];
        return $result;
    }
    
    // Set appropriate back text based on current path as a fallback
    if (strpos($current_path, '/recipes/') !== false) {
        $result['text'] = 'Back to Recipes';
    } else if (strpos($current_path, '/users/profile') !== false) {
        $result['text'] = 'Back to Profile';
    } else if (strpos($current_path, '/users/favorites') !== false) {
        $result['text'] = 'Back to Favorites';
    } else if (strpos($current_path, '/admin/') !== false) {
        $result['text'] = 'Back to Admin Dashboard';
    } else if (strpos($current_path, '/about.php') !== false) {
        $result['text'] = 'Back to About Us';
    } else if (strpos($current_path, '/contact.php') !== false) {
        $result['text'] = 'Back to Contact';
    }
    
    // Fallback to default URL and text
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
    
    if ($type === 'ref_page') {
        // Use ref_page parameter with full paths
        $ref_param = '?ref_page=' . $current_path;
        
        // Collect context parameters
        $context_params = [];
        
        // Add recipe ID if we're on a recipe page
        if (strpos($current_path, '/recipes/show.php') !== false && isset($_GET['id'])) {
            $context_params['id'] = $_GET['id'];
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
    } else {
        // For backward compatibility, use the old ref parameter approach
        if (strpos($current_path, '/recipes/') !== false) {
            $ref_param = '?ref=recipes';
        } elseif (strpos($current_path, '/users/profile.php') !== false) {
            $ref_param = '?ref=profile';
        } elseif (strpos($current_path, '/users/favorites.php') !== false) {
            $ref_param = '?ref=favorites';
        } elseif (strpos($current_path, '/admin/') !== false) {
            $ref_param = '?ref=admin';
        } elseif (strpos($current_path, '/about.php') !== false) {
            $ref_param = '?ref=about';
        } elseif (strpos($current_path, '/index.php') !== false || $current_path == '/') {
            $ref_param = '?ref=home';
        }
        
        // Collect context parameters
        $context_params = [];
        
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
        
        // Add recipe ID if we're on a recipe page
        if (strpos($current_path, '/recipes/show.php') !== false && isset($_GET['id'])) {
            $context_params['recipe_id'] = $_GET['id'];
        }
        
        // Add all context parameters to the ref_param using our helper function
        $ref_param = add_context_to_ref_param($ref_param, $context_params);
    }
    
    return $ref_param;
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
