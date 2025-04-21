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
        '/admin/categories/edit.php' => 'Edit Category',
        '/admin/categories/delete.php' => 'Delete Category',

        // Auth pages
        '/auth/login.php' => 'Login',
        '/auth/register.php' => 'Register'
    ];
    
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
        
        // If no exact match, use pattern matching
        if (!$found_exact_match) {
            // Admin section pattern matching
            if (strpos($ref_page, '/admin/categories/style') !== false) {
                $result['text'] = 'Back to Style Categories';
            } elseif (strpos($ref_page, '/admin/categories/diet') !== false) {
                $result['text'] = 'Back to Diet Categories';
            } elseif (strpos($ref_page, '/admin/categories/type') !== false) {
                $result['text'] = 'Back to Type Categories';
            } elseif (strpos($ref_page, '/admin/categories/measurement') !== false) {
                $result['text'] = 'Back to Measurement Categories';
            } elseif (strpos($ref_page, '/admin/users') !== false) {
                $result['text'] = 'Back to User Management';
            } elseif (strpos($ref_page, '/admin/categories') !== false) {
                $result['text'] = 'Back to Recipe Metadata';
            } elseif (strpos($ref_page, '/admin/') !== false) {
                $result['text'] = 'Back to Admin Dashboard';
            }
            // Recipe section pattern matching
            elseif (strpos($ref_page, '/recipes/index.php') !== false) {
                $result['text'] = 'Back to Recipes';
            } elseif (strpos($ref_page, '/recipes/show.php') !== false) {
                $result['text'] = 'Back to Recipe';
            } elseif (strpos($ref_page, '/recipes/new.php') !== false) {
                $result['text'] = 'Back to Create Recipe';
            } elseif (strpos($ref_page, '/recipes/edit.php') !== false) {
                $result['text'] = 'Back to Edit Recipe';
            } elseif (strpos($ref_page, '/recipes/delete.php') !== false) {
                $result['text'] = 'Back to Delete Recipe';
            }
            // User section pattern matching
            elseif (strpos($ref_page, '/users/favorites.php') !== false) {
                $result['text'] = 'Back to Favorites';
            } elseif (strpos($ref_page, '/users/profile.php') !== false) {
                $result['text'] = 'Back to Profile';
            }
            // Other pages pattern matching
            elseif (strpos($ref_page, '/index.php') !== false) {
                $result['text'] = 'Back to Home';
            } elseif (strpos($ref_page, '/about.php') !== false) {
                $result['text'] = 'Back to About Us';
            } else {
                // Try to find a partial match in the path-to-title mapping as a last resort
                foreach ($path_to_title_map as $path => $title) {
                    if (strpos($ref_page, $path) !== false) {
                        $result['text'] = 'Back to ' . $title;
                        break;
                    }
                }
            }
        }
        return $result;
    }
    
    // Check for ref parameter in query string (second priority)
    $ref = $_GET['ref'] ?? '';
    if ($ref) {
        switch ($ref) {
            case 'home':
                $result['url'] = url_for('/index.php');
                $result['text'] = 'Back to Home';
                return $result;
            case 'recipes':
                $result['url'] = url_for('/recipes/index.php');
                $result['text'] = 'Back to Recipes';
                return $result;
            case 'profile':
                $result['url'] = url_for('/users/profile.php');
                $result['text'] = 'Back to Profile';
                return $result;
            case 'favorites':
                $result['url'] = url_for('/users/favorites.php');
                $result['text'] = 'Back to Favorites';
                return $result;
            case 'gallery':
                $result['url'] = url_for('/recipes/index.php');
                $result['text'] = 'Back to Recipes';
                return $result;
            case 'about':
                $result['url'] = url_for('/about.php');
                $result['text'] = 'Back to About Us';
                return $result;
            case 'admin':
                $result['url'] = url_for('/admin/index.php');
                $result['text'] = 'Back to Admin Dashboard';
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
                
                // If no exact match, use pattern matching as a fallback
                // Admin section pattern matching
                if (strpos($path, '/admin/categories/style') !== false) {
                    $result['text'] = 'Back to Style Categories';
                } else if (strpos($path, '/admin/categories/diet') !== false) {
                    $result['text'] = 'Back to Diet Categories';
                } else if (strpos($path, '/admin/categories/type') !== false) {
                    $result['text'] = 'Back to Type Categories';
                } else if (strpos($path, '/admin/categories/measurement') !== false) {
                    $result['text'] = 'Back to Measurement Categories';
                } else if (strpos($path, '/admin/users') !== false) {
                    $result['text'] = 'Back to User Management';
                } else if (strpos($path, '/admin/categories') !== false) {
                    $result['text'] = 'Back to Recipe Metadata';
                } else if (strpos($path, '/admin') !== false) {
                    $result['text'] = 'Back to Admin Dashboard';
                } 
                // Recipe section pattern matching
                else if (strpos($path, '/recipes/new.php') !== false) {
                    $result['text'] = 'Back to Create Recipe';
                } else if (strpos($path, '/recipes/edit.php') !== false) {
                    $result['text'] = 'Back to Edit Recipe';
                } else if (strpos($path, '/recipes/delete.php') !== false) {
                    $result['text'] = 'Back to Delete Recipe';
                } else if (strpos($path, '/recipes/show.php') !== false) {
                    $result['text'] = 'Back to Recipe';
                } else if (strpos($path, '/recipes') !== false) {
                    $result['text'] = 'Back to Recipes';
                } 
                // User section pattern matching
                else if (strpos($path, '/users/profile') !== false) {
                    $result['text'] = 'Back to Profile';
                } else if (strpos($path, '/users/favorites') !== false) {
                    $result['text'] = 'Back to Favorites';
                } else if (strpos($path, '/users/settings') !== false) {
                    $result['text'] = 'Back to Settings';
                } 
                // Other pages pattern matching
                else if (strpos($path, '/about.php') !== false) {
                    $result['text'] = 'Back to About Us';
                } else if (strpos($path, '/contact.php') !== false) {
                    $result['text'] = 'Back to Contact';
                } else if (strpos($path, '/auth/login.php') !== false || strpos($path, '/login.php') !== false) {
                    $result['text'] = 'Back to Login';
                } else if (strpos($path, '/auth/register.php') !== false || strpos($path, '/register.php') !== false) {
                    $result['text'] = 'Back to Register';
                } else if (strpos($path, '/index.php') !== false || $path == '/' || $path == '') {
                    $result['text'] = 'Back to Home';
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
    
    if ($type === 'ref_page') {
        // Use ref_page parameter with full paths
        $ref_param = '?ref_page=' . $current_path;
        
        // Add recipe ID if we're on a recipe page
        if (strpos($current_path, '/recipes/show.php') !== false && isset($_GET['id'])) {
            $ref_param .= '&id=' . $_GET['id'];
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
        
        // Add recipe ID if we're on a recipe page
        if (strpos($current_path, '/recipes/show.php') !== false && isset($_GET['id'])) {
            if (!empty($ref_param)) {
                $ref_param .= '&';
            } else {
                $ref_param = '?';
            }
            $ref_param .= 'recipe_id=' . $_GET['id'];
        }
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
