/**
 * @fileoverview Member header component functionality for FlavorConnect
 * @author Henry Vaughn
 * @version 1.3.0
 * @license MIT
 * @description Handles the member header dropdown menu functionality.
 * This script manages the user menu dropdown in the member header, including:
 * - Toggle functionality for the dropdown menu
 * - Click outside detection to close the dropdown
 * - Keyboard accessibility (Escape key to close)
 * - ARIA attribute management for accessibility
 * 
 * Note: This script replaces and consolidates the functionality previously split
 * between header.js and member-header.js to avoid redundancy.
 */

document.addEventListener('DOMContentLoaded', () => {
    // User Menu Toggle - Support both old and new menu selectors for backward compatibility
    const userMenuBtn = document.querySelector('.user-menu-button') || document.querySelector('.user-dropdown');
    const dropdownMenu = document.querySelector('.dropdown-menu') || document.querySelector('.user-menu');

    if (userMenuBtn && dropdownMenu) {
        userMenuBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            // Handle ARIA attributes if they exist
            if (userMenuBtn.hasAttribute('aria-expanded')) {
                const isExpanded = userMenuBtn.getAttribute('aria-expanded') === 'true';
                userMenuBtn.setAttribute('aria-expanded', !isExpanded);
            }
            
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdownMenu.contains(e.target) && !userMenuBtn.contains(e.target)) {
                if (userMenuBtn.hasAttribute('aria-expanded')) {
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                }
                dropdownMenu.classList.remove('show');
            }
        });

        // Close dropdown on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                if (userMenuBtn.hasAttribute('aria-expanded')) {
                    userMenuBtn.setAttribute('aria-expanded', 'false');
                }
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
