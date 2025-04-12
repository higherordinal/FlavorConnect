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
      return WWW_ROOT . $script_path;
      
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
 * 1. The HTTP_REFERER if available
 * 2. The 'ref' parameter in the query string
 * 3. A default fallback URL
 * 4. Named routes from the router if available
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
    
    // First check for ref parameter in query string
    $ref = $_GET['ref'] ?? '';
    if ($ref) {
        switch ($ref) {
            case 'recipe':
                // Handle recipe reference - return to the specific recipe page
                if (isset($_GET['recipe_id'])) {
                    $recipe_id = $_GET['recipe_id'];
                    $result['url'] = url_for('/recipes/show.php?id=' . $recipe_id);
                    $result['text'] = 'Back to Recipe';
                    return $result;
                }
                break;
            case 'home':
                $result['url'] = url_for('/index.php');
                $result['text'] = 'Back to Home';
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
            case 'recipes':
                $result['url'] = url_for('/recipes/index.php');
                $result['text'] = 'Back to Recipes';
                return $result;
            // Add more cases as needed
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
                
                // Try to determine a better back text based on the path
                if (strpos($path, '/admin/users') !== false) {
                    $result['text'] = 'Back to User Management';
                } else if (strpos($path, '/admin/categories') !== false) {
                    $result['text'] = 'Back to Recipe Metadata';
                } else if (strpos($path, '/admin') !== false) {
                    $result['text'] = 'Back to Admin';
                } else if (strpos($path, '/recipes/new.php') !== false) {
                    $result['text'] = 'Back to Create Recipe';
                } else if (strpos($path, '/recipes/edit.php') !== false) {
                    $result['text'] = 'Back to Edit Recipe';
                } else if (strpos($path, '/recipes/delete.php') !== false) {
                    $result['text'] = 'Back to Delete Recipe';
                } else if (strpos($path, '/recipes') !== false) {
                    $result['text'] = 'Back to Recipes';
                } else if (strpos($path, '/users/profile') !== false) {
                    $result['text'] = 'Back to Profile';
                } else if (strpos($path, '/users/favorites') !== false) {
                    $result['text'] = 'Back to Favorites';
                }
                
                return $result;
            }
        }
    }
    
    // Try to use router for named routes if available
    global $router;
    if (isset($router)) {
        // Check if we're coming from a recipe page
        if (isset($_SESSION['from_recipe_id']) && is_numeric($_SESSION['from_recipe_id'])) {
            try {
                // Generate URL using named route
                $recipe_url = $router->url('recipes.show', ['id' => $_SESSION['from_recipe_id']]);
                if (!empty($recipe_url)) {
                    $result['url'] = $recipe_url;
                    $result['text'] = 'Back to Recipe';
                    return $result;
                }
            } catch (Exception $e) {
                // If router URL generation fails, continue with default behavior
            }
        }
        
        // Try to determine the named route based on the URL pattern
        $current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        foreach ($router->named_routes as $name => $path) {
            if (strpos($current_path, $path) !== false) {
                // We found a matching named route, use it for context-aware back link text
                if (strpos($name, 'recipes.') === 0) {
                    $result['text'] = 'Back to Recipes';
                } else if (strpos($name, 'users.') === 0) {
                    $result['text'] = 'Back to Profile';
                } else if (strpos($name, 'admin.') === 0) {
                    $result['text'] = 'Back to Admin';
                }
                break;
            }
        }
    }
    
    // Fallback to default URL and text
    return $result;
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
    // Ensure URL is a string before passing to h()
    $back_url = is_array($back_link_data['url']) ? json_encode($back_link_data['url']) : $back_link_data['url'];
    $html .= '<a href="' . h($back_url) . '" class="back-link">';
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

?>
