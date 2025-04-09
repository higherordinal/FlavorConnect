<?php
/**
 * Router Class
 * 
 * Handles URL routing for FlavorConnect application
 * Designed to be gradually adopted without disrupting existing functionality
 */
class Router {
    /** @var array Registered routes */
    private $routes = [];
    
    /** @var array Route patterns for dynamic URLs */
    private $patterns = [];
    
    /** @var array Middleware stack */
    private $middleware = [];
    
    /** @var array Named routes for reverse routing */
    private $named_routes = [];
    
    /** @var string Current group prefix */
    private $current_group_prefix = '';
    
    /** @var array Current group middleware */
    private $current_group_middleware = [];
    
    /**
     * Constructor
     */
    public function __construct() {
        // Initialize with empty routes
    }
    
    /**
     * Register a GET route
     * 
     * @param string $path Route path
     * @param mixed $handler Handler (file path, callback, or [controller, method])
     * @param string $name Optional route name for reverse routing
     * @return Router For method chaining
     */
    public function get($path, $handler, $name = null) {
        return $this->addRoute('GET', $path, $handler, $name);
    }
    
    /**
     * Register a POST route
     * 
     * @param string $path Route path
     * @param mixed $handler Handler (file path, callback, or [controller, method])
     * @param string $name Optional route name for reverse routing
     * @return Router For method chaining
     */
    public function post($path, $handler, $name = null) {
        return $this->addRoute('POST', $path, $handler, $name);
    }
    
    /**
     * Register routes for multiple HTTP methods
     * 
     * @param array $methods HTTP methods
     * @param string $path Route path
     * @param mixed $handler Handler (file path, callback, or [controller, method])
     * @param string $name Optional route name for reverse routing
     * @return Router For method chaining
     */
    public function match(array $methods, $path, $handler, $name = null) {
        foreach ($methods as $method) {
            $this->addRoute($method, $path, $handler, $name);
        }
        return $this;
    }
    
    /**
     * Register a route that responds to any HTTP method
     * 
     * @param string $path Route path
     * @param mixed $handler Handler (file path, callback, or [controller, method])
     * @param string $name Optional route name for reverse routing
     * @return Router For method chaining
     */
    public function any($path, $handler, $name = null) {
        return $this->addRoute('ANY', $path, $handler, $name);
    }
    
    /**
     * Create a route group with a prefix
     * 
     * @param string $prefix URL prefix for all routes in the group
     * @param callable $callback Function that defines routes in this group
     * @param array $middleware Optional middleware for the group
     * @return Router For method chaining
     */
    public function group($prefix, callable $callback, array $middleware = []) {
        // Save current group state
        $previous_prefix = $this->current_group_prefix;
        $previous_middleware = $this->current_group_middleware;
        
        // Set new group state
        $this->current_group_prefix = $previous_prefix . $prefix;
        $this->current_group_middleware = array_merge($previous_middleware, $middleware);
        
        // Execute the callback to define routes in this group
        $callback($this);
        
        // Restore previous group state
        $this->current_group_prefix = $previous_prefix;
        $this->current_group_middleware = $previous_middleware;
        
        return $this;
    }
    
    /**
     * Add middleware to the router
     * 
     * @param string $name Middleware name
     * @param callable $middleware Middleware function
     * @return Router For method chaining
     */
    public function addMiddleware($name, callable $middleware) {
        $this->middleware[$name] = $middleware;
        return $this;
    }
    
    /**
     * Apply middleware to a route
     * 
     * @param array|string $middleware Middleware name(s)
     * @return Router For method chaining
     */
    public function middleware($middleware) {
        if (!is_array($middleware)) {
            $middleware = [$middleware];
        }
        
        $this->current_group_middleware = array_merge($this->current_group_middleware, $middleware);
        return $this;
    }
    
    /**
     * Add a route to the router
     * 
     * @param string $method HTTP method
     * @param string $path Route path
     * @param mixed $handler Handler (file path, callback, or [controller, method])
     * @param string $name Optional route name for reverse routing
     * @return Router For method chaining
     */
    private function addRoute($method, $path, $handler, $name = null) {
        // Apply group prefix if any
        $path = $this->current_group_prefix . $path;
        
        // Normalize path
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        
        // Store the route
        $route = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => $this->current_group_middleware
        ];
        
        // Check if the path contains parameters
        if (strpos($path, '{') !== false) {
            // Convert path to regex pattern
            $pattern = $this->pathToPattern($path);
            $this->patterns[$pattern] = $route;
        } else {
            // Static route
            $key = $method . '|' . $path;
            $this->routes[$key] = $route;
        }
        
        // Store named route if provided
        if ($name !== null) {
            $this->named_routes[$name] = $path;
        }
        
        return $this;
    }
    
    /**
     * Convert a path with parameters to a regex pattern
     * 
     * @param string $path Path with parameters like /users/{id}
     * @return string Regex pattern
     */
    private function pathToPattern($path) {
        // Replace {param} with regex capture groups
        $pattern = preg_replace('/{([^}]+)}/', '(?P<$1>[^/]+)', $path);
        
        // Escape forward slashes and add start/end markers
        return '#^' . $pattern . '$#';
    }
    
    /**
     * Match the current request to a route
     * 
     * @param string $method HTTP method
     * @param string $path Request path
     * @return array|false Route data or false if no match
     */
    public function match_route($method, $path) {
        // Normalize path
        $path = '/' . trim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        
        // Check for exact match first
        $key = $method . '|' . $path;
        if (isset($this->routes[$key])) {
            return [
                'route' => $this->routes[$key],
                'params' => []
            ];
        }
        
        // Check for ANY method match
        $key = 'ANY|' . $path;
        if (isset($this->routes[$key])) {
            return [
                'route' => $this->routes[$key],
                'params' => []
            ];
        }
        
        // Check pattern routes
        foreach ($this->patterns as $pattern => $route) {
            if ($route['method'] === $method || $route['method'] === 'ANY') {
                if (preg_match($pattern, $path, $matches)) {
                    // Filter out numeric keys
                    $params = array_filter($matches, function($key) {
                        return !is_numeric($key);
                    }, ARRAY_FILTER_USE_KEY);
                    
                    return [
                        'route' => $route,
                        'params' => $params
                    ];
                }
            }
        }
        
        return false;
    }
    
    /**
     * Generate URL for a named route
     * 
     * @param string $name Route name
     * @param array $params Route parameters
     * @return string Generated URL
     */
    public function url($name, array $params = []) {
        if (!isset($this->named_routes[$name])) {
            throw new Exception("Route '{$name}' not found");
        }
        
        $url = $this->named_routes[$name];
        
        // Replace parameters in the URL
        foreach ($params as $key => $value) {
            $url = str_replace("{{$key}}", $value, $url);
        }
        
        return $url;
    }
    
    /**
     * Execute the router
     * 
     * @param string $method HTTP method (defaults to current request method)
     * @param string $path Request path (defaults to current request URI)
     * @return mixed Result of the route handler
     */
    public function dispatch($method = null, $path = null) {
        // Use current request if not specified
        if ($method === null) {
            $method = $_SERVER['REQUEST_METHOD'];
        }
        
        if ($path === null) {
            $path = $_SERVER['REQUEST_URI'];
            
            // Extract query string if present
            if (strpos($path, '?') !== false) {
                list($path, $query_string) = explode('?', $path, 2);
            }
            
            // Remove project folder from URI if in XAMPP environment
            if (defined('PROJECT_FOLDER') && !empty(PROJECT_FOLDER)) {
                $path = str_replace(PROJECT_FOLDER, '', $path);
            }
        }
        
        // Match the route
        $match = $this->match_route($method, $path);
        
        if ($match) {
            $route = $match['route'];
            $params = $match['params'];
            
            // Execute middleware stack
            $handler = $this->wrapWithMiddleware($route);
            
            // Execute the handler
            return $this->executeHandler($handler, $params);
        }
        
        // No route found, return false to allow fallback to legacy routing
        return false;
    }
    
    /**
     * Wrap the route handler with middleware
     * 
     * @param array $route Route data
     * @return callable Wrapped handler
     */
    private function wrapWithMiddleware($route) {
        $handler = function($params) use ($route) {
            return $this->executeHandler($route['handler'], $params);
        };
        
        // Apply middleware in reverse order (last in, first out)
        if (!empty($route['middleware'])) {
            $middleware_stack = array_reverse($route['middleware']);
            
            foreach ($middleware_stack as $middleware_name) {
                if (isset($this->middleware[$middleware_name])) {
                    $middleware = $this->middleware[$middleware_name];
                    $next = $handler;
                    
                    $handler = function($params) use ($middleware, $next) {
                        return $middleware($params, $next);
                    };
                }
            }
        }
        
        return $handler;
    }
    
    /**
     * Execute a route handler
     * 
     * @param mixed $handler Route handler
     * @param array $params Route parameters
     * @return mixed Result of the handler
     */
    private function executeHandler($handler, $params) {
        // If handler is a string, treat it as a file path
        if (is_string($handler)) {
            // Make params available to the included file
            extract($params);
            
            // Include the file
            $file_path = PUBLIC_PATH . '/' . $handler;
            if (file_exists($file_path)) {
                return include($file_path);
            } else {
                throw new Exception("Handler file not found: {$file_path}");
            }
        }
        
        // If handler is a callable, execute it with parameters
        if (is_callable($handler)) {
            return call_user_func($handler, $params);
        }
        
        // If handler is an array [Controller, method], instantiate and call
        if (is_array($handler) && count($handler) === 2) {
            list($controller, $method) = $handler;
            
            // If controller is a string, instantiate it
            if (is_string($controller)) {
                $controller = new $controller();
            }
            
            // Call the method with parameters
            return call_user_func_array([$controller, $method], [$params]);
        }
        
        throw new Exception("Invalid route handler");
    }
    
    /**
     * Load routes from a configuration file
     * 
     * @param string $file Path to routes configuration file
     * @return Router For method chaining
     */
    public function loadRoutes($file) {
        $router = $this;
        require($file);
        return $this;
    }
    
    /**
     * Cache routes to a file for faster loading
     * 
     * @param string $file Path to cache file
     * @return Router For method chaining
     */
    public function cacheRoutes($file) {
        $cache = [
            'routes' => $this->routes,
            'patterns' => $this->patterns,
            'named_routes' => $this->named_routes
        ];
        
        $content = '<?php return ' . var_export($cache, true) . ';';
        file_put_contents($file, $content);
        
        return $this;
    }
    
    /**
     * Load routes from a cache file
     * 
     * @param string $file Path to cache file
     * @return Router For method chaining
     */
    public function loadCachedRoutes($file) {
        $cache = require($file);
        
        $this->routes = $cache['routes'];
        $this->patterns = $cache['patterns'];
        $this->named_routes = $cache['named_routes'];
        
        return $this;
    }
}
