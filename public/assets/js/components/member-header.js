/**
 * @fileoverview Member header component functionality for FlavorConnect
 * @author FlavorConnect Team
 * @version 1.0.0
 * @license MIT
 */

document.addEventListener('DOMContentLoaded', () => {
    // User Menu Toggle
    const userMenuBtn = document.querySelector('.user-menu-button');
    const dropdownMenu = document.querySelector('.dropdown-menu');

    if (userMenuBtn && dropdownMenu) {
        userMenuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isExpanded = userMenuBtn.getAttribute('aria-expanded') === 'true';
            userMenuBtn.setAttribute('aria-expanded', !isExpanded);
            dropdownMenu.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdownMenu.contains(e.target) && !userMenuBtn.contains(e.target)) {
                userMenuBtn.setAttribute('aria-expanded', 'false');
                dropdownMenu.classList.remove('show');
            }
        });

        // Close dropdown on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && dropdownMenu.classList.contains('show')) {
                userMenuBtn.setAttribute('aria-expanded', 'false');
                dropdownMenu.classList.remove('show');
            }
        });
    }
});
