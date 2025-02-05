-- Create recipe_favorites table
CREATE TABLE IF NOT EXISTS recipe_favorites (
    favorite_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recipe_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    -- Ensure a user can only favorite a recipe once
    UNIQUE KEY unique_favorite (user_id, recipe_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
