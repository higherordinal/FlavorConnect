<?php

/**
 * Pagination Class
 * 
 * Handles pagination for database results
 */
class Pagination {
    /** @var int Current page number */
    private $current_page;
    
    /** @var int Number of items per page */
    private $per_page;
    
    /** @var int Total number of items */
    private $total_count;
    
    /**
     * Constructor
     * 
     * @param int $page Current page number (default: 1)
     * @param int $per_page Number of items per page (default: 10)
     * @param int $total_count Total number of items
     */
    public function __construct($page = 1, $per_page = 10, $total_count = 0) {
        $this->current_page = (int) $page;
        $this->per_page = (int) $per_page;
        $this->total_count = (int) $total_count;
    }
    
    /**
     * Get the offset for SQL LIMIT clause
     * 
     * @return int Offset value
     */
    public function offset() {
        return ($this->current_page - 1) * $this->per_page;
    }
    
    /**
     * Get the total number of pages
     * 
     * @return int Total pages
     */
    public function total_pages() {
        return ceil($this->total_count / $this->per_page);
    }
    
    /**
     * Check if there is a previous page
     * 
     * @return bool True if there is a previous page
     */
    public function has_previous_page() {
        return $this->current_page > 1;
    }
    
    /**
     * Check if there is a next page
     * 
     * @return bool True if there is a next page
     */
    public function has_next_page() {
        return $this->current_page < $this->total_pages();
    }
    
    /**
     * Get the previous page number
     * 
     * @return int Previous page number
     */
    public function previous_page() {
        return $this->current_page - 1;
    }
    
    /**
     * Get the next page number
     * 
     * @return int Next page number
     */
    public function next_page() {
        return $this->current_page + 1;
    }
    
    /**
     * Generate pagination links HTML
     * 
     * @param string $url_pattern URL pattern with {page} placeholder
     * @param array $extra_params Additional URL parameters
     * @param bool $recipe_gallery Whether to use recipe gallery style pagination
     * @return string HTML for pagination links
     */
    public function page_links($url_pattern, $extra_params = [], $recipe_gallery = false) {
        $total_pages = $this->total_pages();
        
        if($total_pages <= 1) {
            return '';
        }
        
        // Build query string for extra parameters
        $query_string = '';
        if(!empty($extra_params)) {
            foreach($extra_params as $key => $value) {
                $query_string .= "&{$key}=" . urlencode($value);
            }
        }
        
        // Use recipe gallery style pagination if requested
        if($recipe_gallery) {
            return $this->recipe_gallery_pagination($url_pattern, $query_string, $total_pages);
        }
        
        $html = '<div class="pagination">';
        
        // First page
        if($this->has_previous_page()) {
            $html .= '<a href="' . str_replace('{page}', '1', $url_pattern) . $query_string . '" class="pagination-link first" title="First page">';
            $html .= '<i class="fas fa-angle-double-left"></i>';
            $html .= '</a>';
        } else {
            $html .= '<span class="pagination-link disabled first">';
            $html .= '<i class="fas fa-angle-double-left"></i>';
            $html .= '</span>';
        }
        
        // Previous page
        if($this->has_previous_page()) {
            $html .= '<a href="' . str_replace('{page}', $this->previous_page(), $url_pattern) . $query_string . '" class="pagination-link prev" title="Previous page">';
            $html .= '<i class="fas fa-angle-left"></i>';
            $html .= '</a>';
        } else {
            $html .= '<span class="pagination-link disabled prev">';
            $html .= '<i class="fas fa-angle-left"></i>';
            $html .= '</span>';
        }
        
        // Page numbers
        $html .= '<span class="pagination-info">';
        $html .= 'Page ' . $this->current_page . ' of ' . $total_pages;
        $html .= '</span>';
        
        // Next page
        if($this->has_next_page()) {
            $html .= '<a href="' . str_replace('{page}', $this->next_page(), $url_pattern) . $query_string . '" class="pagination-link next" title="Next page">';
            $html .= '<i class="fas fa-angle-right"></i>';
            $html .= '</a>';
        } else {
            $html .= '<span class="pagination-link disabled next">';
            $html .= '<i class="fas fa-angle-right"></i>';
            $html .= '</span>';
        }
        
        // Last page
        if($this->has_next_page()) {
            $html .= '<a href="' . str_replace('{page}', $total_pages, $url_pattern) . $query_string . '" class="pagination-link last" title="Last page">';
            $html .= '<i class="fas fa-angle-double-right"></i>';
            $html .= '</a>';
        } else {
            $html .= '<span class="pagination-link disabled last">';
            $html .= '<i class="fas fa-angle-double-right"></i>';
            $html .= '</span>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Generate pagination links HTML specifically for recipe gallery
     * 
     * @param string $url_pattern URL pattern with {page} placeholder
     * @param string $query_string Additional query string parameters
     * @param int $total_pages Total number of pages
     * @return string HTML for pagination links
     */
    private function recipe_gallery_pagination($url_pattern, $query_string, $total_pages) {
        $html = '<nav class="pagination" role="navigation" aria-label="Recipe pages">';
        
        // Previous page link
        if($this->has_previous_page()) {
            $html .= '<a href="' . str_replace('{page}', $this->previous_page(), $url_pattern) . $query_string . '" '
                  . 'class="page-link" '
                  . 'aria-label="Go to previous page">';
            $html .= '<i class="fas fa-chevron-left" aria-hidden="true"></i>';
            $html .= '<span class="sr-only">Previous page</span>';
            $html .= '</a>';
        }
        
        // Page number links
        for($i = 1; $i <= $total_pages; $i++) {
            if(
                $i <= 2 || 
                $i >= $total_pages - 1 || 
                ($i >= $this->current_page - 1 && $i <= $this->current_page + 1)
            ) {
                $html .= '<a href="' . str_replace('{page}', $i, $url_pattern) . $query_string . '" '
                      . 'class="page-link ' . ($i === $this->current_page ? 'active' : '') . '" '
                      . 'aria-label="Go to page ' . $i . '" '
                      . ($i === $this->current_page ? 'aria-current="page"' : '') . '>';
                $html .= $i;
                $html .= '</a>';
            } elseif(
                $i === 3 || 
                $i === $total_pages - 2
            ) {
                $html .= '<span class="page-link" aria-hidden="true">...</span>';
            }
        }
        
        // Next page link
        if($this->has_next_page()) {
            $html .= '<a href="' . str_replace('{page}', $this->next_page(), $url_pattern) . $query_string . '" '
                  . 'class="page-link" '
                  . 'aria-label="Go to next page">';
            $html .= '<i class="fas fa-chevron-right" aria-hidden="true"></i>';
            $html .= '<span class="sr-only">Next page</span>';
            $html .= '</a>';
        }
        
        $html .= '</nav>';
        
        return $html;
    }
}
