document.addEventListener('DOMContentLoaded', function() {
    // Get all favorite buttons
    const favoriteButtons = document.querySelectorAll('.favorite-btn');

    // Add click event listener to each button
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const recipeId = this.dataset.recipeId;
            const isFavorited = this.dataset.isFavorited === 'true';
            const heartIcon = this.querySelector('.fa-heart');

            // Toggle favorite status
            fetch('/recipes/toggle_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `recipe_id=${recipeId}&is_favorited=${!isFavorited}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button state
                    this.dataset.isFavorited = (!isFavorited).toString();
                    this.classList.toggle('active');
                    heartIcon.classList.toggle('fas');
                    heartIcon.classList.toggle('far');
                } else {
                    console.error('Failed to toggle favorite:', data.message);
                }
            })
            .catch(error => {
                console.error('Error toggling favorite:', error);
            });
        });
    });
});
