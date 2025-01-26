-- Database credentials and creation
DROP DATABASE IF EXISTS flavorconnect;
CREATE DATABASE flavorconnect;
USE flavorconnect;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS 'hcvaughn'@'localhost' IDENTIFIED BY '@connect4Establish';
GRANT ALL PRIVILEGES ON flavorconnect.* TO 'hcvaughn'@'localhost';
FLUSH PRIVILEGES;

-- Create tables

-- Create user_account table
CREATE TABLE user_account (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_level ENUM('s', 'a', 'u') NOT NULL DEFAULT 'u',
    is_active BOOLEAN DEFAULT TRUE,
    INDEX idx_user_is_active (is_active)
);

-- Create recipe_style table
CREATE TABLE recipe_style (
    style_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_recipe_style_name (name)
);

-- Create recipe_diet table
CREATE TABLE recipe_diet (
    diet_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_recipe_diet_name (name)
);

-- Create recipe_type table
CREATE TABLE recipe_type (
    type_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    INDEX idx_recipe_type_name (name)
);

-- Create measurement table
CREATE TABLE measurement (
    measurement_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

-- Create recipe table
CREATE TABLE recipe (
    recipe_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    type_id INT UNSIGNED,
    style_id INT UNSIGNED,
    diet_id INT UNSIGNED,
    prep_hours INT DEFAULT 0,
    prep_minutes INT DEFAULT 0,
    cook_hours INT DEFAULT 0,
    cook_minutes INT DEFAULT 0,
    video_url VARCHAR(255),
    img_file_path VARCHAR(255),
    alt_text VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    created_date DATE DEFAULT CURRENT_DATE,
    created_time TIME DEFAULT CURRENT_TIME,
    FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE,
    FOREIGN KEY (type_id) REFERENCES recipe_type(type_id) ON DELETE SET NULL,
    FOREIGN KEY (style_id) REFERENCES recipe_style(style_id) ON DELETE SET NULL,
    FOREIGN KEY (diet_id) REFERENCES recipe_diet(diet_id) ON DELETE SET NULL,
    INDEX idx_recipe_title (title),
    INDEX idx_recipe_featured (is_featured),
    INDEX idx_recipe_type_diet_style (type_id, diet_id, style_id)
);

-- Create ingredient table
CREATE TABLE ingredient (
    ingredient_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    UNIQUE (recipe_id, name),
    INDEX idx_recipe_id (recipe_id),
    INDEX idx_name (name)
);

-- Create recipe_ingredient table
CREATE TABLE recipe_ingredient (
    recipe_ingredient_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    ingredient_id INT UNSIGNED NOT NULL,
    measurement_id INT UNSIGNED NOT NULL,
    quantity DECIMAL(10,2) UNSIGNED NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredient(ingredient_id) ON DELETE CASCADE,
    FOREIGN KEY (measurement_id) REFERENCES measurement(measurement_id) ON DELETE RESTRICT,
    INDEX idx_recipe_id (recipe_id),
    INDEX idx_ingredient_id (ingredient_id)
);

-- Create recipe_step table
CREATE TABLE recipe_step (
    step_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    step_number INT NOT NULL,
    instruction VARCHAR(255) NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    INDEX idx_step_recipe (recipe_id)
);

-- Create tag table
CREATE TABLE tag (
    tag_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    user_id INT UNSIGNED,
    UNIQUE (name, user_id),
    FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE SET NULL
);

-- Create recipe_tag table
CREATE TABLE recipe_tag (
    recipe_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (recipe_id, tag_id),
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tag(tag_id) ON DELETE CASCADE,
    INDEX idx_tag_recipe (recipe_id, tag_id)
);

-- Create recipe_comment table
CREATE TABLE recipe_comment (
    comment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    comment_text VARCHAR(255) NOT NULL,
    created_date DATE DEFAULT CURRENT_DATE,
    created_time TIME DEFAULT CURRENT_TIME,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE,
    INDEX idx_comment_recipe (recipe_id)
);

-- Create recipe_rating table
CREATE TABLE recipe_rating (
    rating_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    rating_value TINYINT NOT NULL CHECK (rating_value BETWEEN 1 AND 5),
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE,
    INDEX idx_rating_recipe_user (recipe_id, user_id)
);

-- Create user_favorite table
CREATE TABLE user_favorite (
    user_id INT UNSIGNED NOT NULL,
    recipe_id INT UNSIGNED NOT NULL,
    added_date DATE DEFAULT CURRENT_DATE,
    PRIMARY KEY (user_id, recipe_id),
    FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE
);

-- Insert predefined measurements
INSERT INTO measurement (name) VALUES
('cup'), ('teaspoon'), ('tablespoon'), ('gram'), ('kilogram'), ('ounce'), ('pound'), ('pinch'), ('dash'), ('liter'), ('milliliter');

-- Insert default recipe styles
INSERT INTO recipe_style (name) VALUES
('American'), ('Italian'), ('Mexican'), ('Chinese'), ('Indian'), ('Japanese'), ('Thai'), ('Mediterranean'), ('French'), ('Korean');

-- Insert default recipe diets
INSERT INTO recipe_diet (name) VALUES
('Vegetarian'), ('Vegan'), ('Gluten-Free'), ('Dairy-Free'), ('Keto'), ('Paleo'), ('Low-Carb'), ('Low-Fat'), ('Pescatarian'), ('Halal');

-- Insert default recipe types
INSERT INTO recipe_type (name) VALUES
('Breakfast'), ('Lunch'), ('Dinner'), ('Appetizer'), ('Dessert'), ('Snack'), ('Beverage'), ('Soup'), ('Salad'), ('Main Course');

-- Insert sample users with different levels
INSERT INTO user_account (username, first_name, last_name, email, password_hash, user_level) VALUES
('super_admin', 'Henry', 'Vaughn', 'henrycvaughn@students.abtech.edu', 'Divided4union', 's'),
('admin_chef', 'Michael', 'Brown', 'michael@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'a'),
('chef_maria', 'Maria', 'Garcia', 'maria@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u'),
('home_cook', 'David', 'Wilson', 'david@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u'),
('foodie_jane', 'Jane', 'Smith', 'jane@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u');

-- Insert some tags
INSERT INTO tag (name, user_id) VALUES
('Quick & Easy', 1),
('Vegetarian', 1),
('Breakfast', 1),
('Comfort Food', 2),
('Healthy', 2),
('Italian', 2),
('Mexican', 3),
('Dessert', 3);

-- Insert sample recipes
INSERT INTO recipe (user_id, title, description, type_id, style_id, diet_id, prep_hours, prep_minutes, cook_hours, cook_minutes, is_featured) VALUES
((SELECT user_id FROM user_account WHERE username = 'chef_maria'), 
 'Classic Spaghetti Carbonara', 'Traditional Italian pasta dish with eggs, cheese, pancetta, and black pepper', 
   (SELECT type_id FROM recipe_type WHERE name = 'Main Course'),
   (SELECT style_id FROM recipe_style WHERE name = 'Italian'),
   NULL, 0, 15, 0, 20, TRUE),
   
((SELECT user_id FROM user_account WHERE username = 'home_cook'), 
 'Breakfast Burrito', 'Hearty breakfast wrapped in a warm tortilla',
   (SELECT type_id FROM recipe_type WHERE name = 'Breakfast'),
   (SELECT style_id FROM recipe_style WHERE name = 'Mexican'),
   NULL, 0, 10, 0, 15, FALSE),
   
((SELECT user_id FROM user_account WHERE username = 'admin_chef'), 
 'Vegetarian Buddha Bowl', 'Nutritious bowl filled with grains, vegetables, and tahini dressing',
   (SELECT type_id FROM recipe_type WHERE name = 'Main Course'),
   NULL,
   (SELECT diet_id FROM recipe_diet WHERE name = 'Vegetarian'),
   0, 20, 0, 25, TRUE),
   
((SELECT user_id FROM user_account WHERE username = 'foodie_jane'), 
 'Quick Chocolate Mug Cake', '5-minute microwave chocolate cake in a mug',
   (SELECT type_id FROM recipe_type WHERE name = 'Dessert'),
   NULL,
   NULL, 0, 5, 0, 2, FALSE),
   
((SELECT user_id FROM user_account WHERE username = 'super_admin'), 
 'Mediterranean Quinoa Salad', 'Fresh and healthy quinoa salad with Mediterranean flavors',
   (SELECT type_id FROM recipe_type WHERE name = 'Salad'),
   (SELECT style_id FROM recipe_style WHERE name = 'Mediterranean'),
   (SELECT diet_id FROM recipe_diet WHERE name = 'Vegetarian'),
   0, 15, 0, 20, TRUE),
   
((SELECT user_id FROM user_account WHERE username = 'admin_chef'), 
 'Classic Margherita Pizza', 'Simple and delicious traditional Italian pizza',
   (SELECT type_id FROM recipe_type WHERE name = 'Main Course'),
   (SELECT style_id FROM recipe_style WHERE name = 'Italian'),
   (SELECT diet_id FROM recipe_diet WHERE name = 'Vegetarian'),
   0, 30, 0, 15, TRUE);

-- Tag the recipes
INSERT INTO recipe_tag (recipe_id, tag_id) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 
 (SELECT tag_id FROM tag WHERE name = 'Italian')),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 
 (SELECT tag_id FROM tag WHERE name = 'Comfort Food')),
 
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 
 (SELECT tag_id FROM tag WHERE name = 'Quick & Easy')),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 
 (SELECT tag_id FROM tag WHERE name = 'Breakfast')),
 
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 
 (SELECT tag_id FROM tag WHERE name = 'Healthy')),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 
 (SELECT tag_id FROM tag WHERE name = 'Vegetarian')),
 
((SELECT recipe_id FROM recipe WHERE title = 'Quick Chocolate Mug Cake'), 
 (SELECT tag_id FROM tag WHERE name = 'Quick & Easy')),
((SELECT recipe_id FROM recipe WHERE title = 'Quick Chocolate Mug Cake'), 
 (SELECT tag_id FROM tag WHERE name = 'Dessert')),
 
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 
 (SELECT tag_id FROM tag WHERE name = 'Healthy')),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 
 (SELECT tag_id FROM tag WHERE name = 'Vegetarian')),
 
((SELECT recipe_id FROM recipe WHERE title = 'Classic Margherita Pizza'), 
 (SELECT tag_id FROM tag WHERE name = 'Italian')),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Margherita Pizza'), 
 (SELECT tag_id FROM tag WHERE name = 'Vegetarian'));
