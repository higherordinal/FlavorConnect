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
     * @return string HTML for pagination links
     */
    public function page_links($url_pattern, $extra_params = []) {
        $total_pages = $this->total_pages();
        
        if($total_pages <= 1) {
            return '';
        }
        
        $query_string = '';
        if(!empty($extra_params)) {
            foreach($extra_params as $key => $value) {
                if($value !== null && $value !== '') {
                    $query_string .= "&{$key}=" . urlencode($value);
                }
            }
        }
        
        $html = '<div class="pagination">';
        
        $this->add_link($html, $url_pattern, $query_string, 'first', '1', $this->has_previous_page());
        $this->add_link($html, $url_pattern, $query_string, 'prev', $this->previous_page(), $this->has_previous_page());
        
        $html .= '<span class="pagination-info">';
        $html .= 'Page ' . $this->current_page . ' of ' . $total_pages;
        $html .= '</span>';
        
        $this->add_link($html, $url_pattern, $query_string, 'next', $this->next_page(), $this->has_next_page());
        $this->add_link($html, $url_pattern, $query_string, 'last', $total_pages, $this->has_next_page());
        
        $html .= '</div>';
        
        return $html;
    }
    
    private function add_link(&$html, $url_pattern, $query_string, $class, $page, $enabled) {
        if($enabled) {
            $html .= '<a href="' . str_replace('{page}', $page, $url_pattern) . $query_string . '" class="pagination-link ' . $class . '" title="' . ucfirst($class) . ' page">';
        } else {
            $html .= '<span class="pagination-link disabled ' . $class . '">';
        }
        
        switch($class) {
            case 'first':
                $html .= '<i class="fas fa-angle-double-left"></i>';
                break;
            case 'prev':
                $html .= '<i class="fas fa-angle-left"></i>';
                break;
            case 'next':
                $html .= '<i class="fas fa-angle-right"></i>';
                break;
            case 'last':
                $html .= '<i class="fas fa-angle-double-right"></i>';
                break;
        }
        
        if($enabled) {
            $html .= '</a>';
        } else {
            $html .= '</span>';
        }
    }
}
