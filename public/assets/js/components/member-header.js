/**
 * @fileoverview Member header component functionality for FlavorConnect
 * @author FlavorConnect Team
 * @version 1.0.0
 * @license MIT
 */

/**
 * Initializes member header functionality
 */
function initializeMemberHeader() {
    setupDropdownToggle();
    setupNotifications();
}

/**
 * Sets up user dropdown toggle functionality
 */
function setupDropdownToggle() {
    const dropdownToggle = document.querySelector('.member-dropdown-toggle');
    const dropdown = document.querySelector('.member-dropdown');

    if (dropdownToggle && dropdown) {
        dropdownToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdown.contains(e.target) && !dropdownToggle.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }
}

/**
 * Sets up notification functionality
 */
function setupNotifications() {
    const notificationToggle = document.querySelector('.notification-toggle');
    const notificationPanel = document.querySelector('.notification-panel');
    const notificationCount = document.querySelector('.notification-count');

    if (notificationToggle && notificationPanel) {
        // Toggle notification panel
        notificationToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            notificationPanel.classList.toggle('show');
            
            if (notificationPanel.classList.contains('show')) {
                markNotificationsAsRead();
            }
        });

        // Close panel when clicking outside
        document.addEventListener('click', (e) => {
            if (!notificationPanel.contains(e.target) && !notificationToggle.contains(e.target)) {
                notificationPanel.classList.remove('show');
            }
        });
    }
}

/**
 * Marks notifications as read and updates UI
 */
async function markNotificationsAsRead() {
    const notificationCount = document.querySelector('.notification-count');
    if (!notificationCount) return;

    try {
        const response = await fetch('/FlavorConnect/public/notifications/mark-read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        if (response.ok) {
            notificationCount.style.display = 'none';
        }
    } catch (error) {
        console.error('Error marking notifications as read:', error);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeMemberHeader);
