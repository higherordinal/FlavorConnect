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
    INDEX idx_user_username (username),
    INDEX idx_user_email (email),
    INDEX idx_user_is_active (is_active),
    INDEX idx_user_last_name (last_name),
    INDEX idx_user_level_lastname (user_level, last_name),
    FULLTEXT INDEX idx_user_name (first_name, last_name)
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
    description TEXT,
    type_id INT UNSIGNED,
    style_id INT UNSIGNED,
    diet_id INT UNSIGNED,
    prep_time INT DEFAULT 0,
    cook_time INT DEFAULT 0,
    video_url VARCHAR(255),
    img_file_path VARCHAR(255),
    alt_text VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    instruction TEXT NOT NULL,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE,
    INDEX idx_step_recipe (recipe_id)
);

-- Create recipe_comment table
CREATE TABLE recipe_comment (
    comment_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipe_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, recipe_id),
    FOREIGN KEY (user_id) REFERENCES user_account(user_id) ON DELETE CASCADE,
    FOREIGN KEY (recipe_id) REFERENCES recipe(recipe_id) ON DELETE CASCADE
);

-- Insert predefined measurements
INSERT INTO measurement (measurement_id, name) VALUES
(1,'cup'),
(2,'tablespoon'),
(3,'teaspoon'),
(4,'pound'),
(5,'ounce'),
(6,'gram'),
(7,'milliliter'),
(8,'pinch'),
(9,'piece'),
(10,'whole'),
(11,'to taste'),
(12,'clove');

-- Insert default recipe styles
INSERT INTO recipe_style (name) VALUES
('American'), ('Italian'), ('Mexican'), ('Chinese'), ('Indian'), ('Japanese'), ('Thai'), ('Mediterranean'), ('French'), ('Korean'), ('Asian');

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

-- Insert sample recipes
INSERT INTO recipe (user_id, title, description, type_id, style_id, diet_id, prep_time, cook_time, is_featured) VALUES
((SELECT user_id FROM user_account WHERE username = 'chef_maria'),
 'Classic Spaghetti Carbonara',
 'A traditional Italian pasta dish made with eggs, cheese, pancetta, and black pepper.',
 (SELECT type_id FROM recipe_type WHERE name = 'Main Course'),
 (SELECT style_id FROM recipe_style WHERE name = 'Italian'),
 (SELECT diet_id FROM recipe_diet WHERE name = 'Low-Fat'),
 1200, 1800, TRUE),

((SELECT user_id FROM user_account WHERE username = 'home_cook'),
 'Breakfast Burrito',
 'A hearty breakfast wrap filled with scrambled eggs, cheese, and fresh vegetables.',
 (SELECT type_id FROM recipe_type WHERE name = 'Breakfast'),
 (SELECT style_id FROM recipe_style WHERE name = 'Mexican'),
 (SELECT diet_id FROM recipe_diet WHERE name = 'Low-Carb'),
 900, 600, FALSE),

((SELECT user_id FROM user_account WHERE username = 'foodie_jane'),
 'Vegetarian Buddha Bowl',
 'A nourishing bowl of grains, roasted vegetables, and protein-rich toppings.',
 (SELECT type_id FROM recipe_type WHERE name = 'Main Course'),
 (SELECT style_id FROM recipe_style WHERE name = 'Asian'),
 (SELECT diet_id FROM recipe_diet WHERE name = 'Vegetarian'),
 1800, 2400, TRUE),

((SELECT user_id FROM user_account WHERE username = 'admin_chef'),
 'Mediterranean Quinoa Salad',
 'A refreshing salad with quinoa, fresh vegetables, and Mediterranean flavors.',
 (SELECT type_id FROM recipe_type WHERE name = 'Salad'),
 (SELECT style_id FROM recipe_style WHERE name = 'Mediterranean'),
 (SELECT diet_id FROM recipe_diet WHERE name = 'Vegetarian'),
 1200, 1800, FALSE),

((SELECT user_id FROM user_account WHERE username = 'chef_maria'),
 'Spicy Thai Curry',
 'A flavorful and aromatic curry with vegetables and tofu in coconut milk.',
 (SELECT type_id FROM recipe_type WHERE name = 'Main Course'),
 (SELECT style_id FROM recipe_style WHERE name = 'Thai'),
 (SELECT diet_id FROM recipe_diet WHERE name = 'Vegan'),
 1500, 2400, TRUE);

-- Insert ingredients for Classic Spaghetti Carbonara
INSERT INTO ingredient (recipe_id, name) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 'spaghetti'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 'eggs'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 'pecorino romano cheese'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 'pancetta'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 'black pepper'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 'salt');

INSERT INTO recipe_ingredient (recipe_id, ingredient_id, measurement_id, quantity) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'spaghetti' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara')),
 (SELECT measurement_id FROM measurement WHERE name = 'pound'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'eggs' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 4),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'pecorino romano cheese' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'pancetta' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara')),
 (SELECT measurement_id FROM measurement WHERE name = 'ounce'),
 8),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'black pepper' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara')),
 (SELECT measurement_id FROM measurement WHERE name = 'teaspoon'),
 2),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'salt' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara')),
 (SELECT measurement_id FROM measurement WHERE name = 'teaspoon'),
 1);

INSERT INTO recipe_step (recipe_id, step_number, instruction) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 1, 'Bring a large pot of salted water to boil for the pasta.'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 2, 'While water is heating, cut the pancetta into small cubes.'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 3, 'In a bowl, whisk together eggs, grated pecorino cheese, and black pepper.'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 4, 'Cook spaghetti in the boiling water according to package instructions.'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 5, 'While pasta cooks, sauté pancetta in a large pan until crispy.'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 6, 'Reserve 1 cup pasta water, then drain pasta.'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 7, 'Working quickly, add hot pasta to pancetta, then mix in egg mixture, stirring constantly.'),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'), 8, 'Add pasta water as needed to create a creamy sauce. Serve immediately.');

-- Insert ingredients for Breakfast Burrito
INSERT INTO ingredient (recipe_id, name) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 'large tortillas'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 'eggs'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 'black beans'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 'cheddar cheese'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 'salsa'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 'avocado'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 'salt'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 'pepper');

INSERT INTO recipe_ingredient (recipe_id, ingredient_id, measurement_id, quantity) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'large tortillas' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 4),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'eggs' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 6),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'black beans' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'cheddar cheese' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'salsa' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 0.5),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'avocado' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1);

INSERT INTO recipe_step (recipe_id, step_number, instruction) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 1, 'Warm the tortillas in a large skillet.'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 2, 'Scramble the eggs with salt and pepper until just set.'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 3, 'Heat the black beans in a small saucepan.'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 4, 'Slice the avocado.'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 5, 'Layer each tortilla with eggs, beans, cheese, avocado, and salsa.'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 6, 'Roll up tightly, tucking in the sides as you go.'),
((SELECT recipe_id FROM recipe WHERE title = 'Breakfast Burrito'), 7, 'Optional: Return to skillet and brown on all sides.');

-- Insert ingredients for Vegetarian Buddha Bowl
INSERT INTO ingredient (recipe_id, name) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'quinoa'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'sweet potato'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'chickpeas'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'kale'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'avocado'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'tahini'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'lemon juice'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'olive oil'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'salt'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 'pepper');

INSERT INTO recipe_ingredient (recipe_id, ingredient_id, measurement_id, quantity) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'quinoa' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'sweet potato' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 2),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'chickpeas' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'kale' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 2),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'avocado' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'tahini' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl')),
 (SELECT measurement_id FROM measurement WHERE name = 'tablespoon'),
 2),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'lemon juice' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl')),
 (SELECT measurement_id FROM measurement WHERE name = 'tablespoon'),
 1);

INSERT INTO recipe_step (recipe_id, step_number, instruction) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 1, 'Cook quinoa according to package instructions.'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 2, 'Roast sweet potato cubes with olive oil, salt, and pepper.'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 3, 'Season and roast chickpeas until crispy.'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 4, 'Massage kale with olive oil and salt.'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 5, 'Make dressing by whisking tahini, lemon juice, and water.'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 6, 'Assemble bowls with quinoa base, topped with vegetables.'),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'), 7, 'Add sliced avocado and drizzle with tahini dressing.');

-- Insert ingredients for Mediterranean Quinoa Salad
INSERT INTO ingredient (recipe_id, name) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'quinoa'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'cucumber'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'cherry tomatoes'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'red onion'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'kalamata olives'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'feta cheese'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'olive oil'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'lemon juice'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'fresh parsley'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'salt'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 'pepper');

INSERT INTO recipe_ingredient (recipe_id, ingredient_id, measurement_id, quantity) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'quinoa' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1.5),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'cucumber' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'cherry tomatoes' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'red onion' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 0.5),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'kalamata olives' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 0.5),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'feta cheese' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 0.75),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'olive oil' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad')),
 (SELECT measurement_id FROM measurement WHERE name = 'tablespoon'),
 3),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'lemon juice' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad')),
 (SELECT measurement_id FROM measurement WHERE name = 'tablespoon'),
 2);

INSERT INTO recipe_step (recipe_id, step_number, instruction) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 1, 'Cook quinoa according to package instructions, let cool.'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 2, 'Dice cucumber and quarter cherry tomatoes.'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 3, 'Finely slice red onion and chop parsley.'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 4, 'In a large bowl, combine cooled quinoa with all chopped vegetables.'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 5, 'Add olives and crumbled feta cheese.'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 6, 'Whisk together olive oil, lemon juice, salt, and pepper.'),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'), 7, 'Pour dressing over salad and toss to combine. Serve chilled.');

-- Insert ingredients for Spicy Thai Curry
INSERT INTO ingredient (recipe_id, name) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'coconut milk'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'red curry paste'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'tofu'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'bell peppers'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'bamboo shoots'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'carrots'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'broccoli'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'soy sauce'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'brown sugar'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'lime juice'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'basil leaves'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 'jasmine rice');

INSERT INTO recipe_ingredient (recipe_id, ingredient_id, measurement_id, quantity) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'coconut milk' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 2),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'red curry paste' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry')),
 (SELECT measurement_id FROM measurement WHERE name = 'tablespoon'),
 2),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'tofu' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 2),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'bell peppers' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'bamboo shoots' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'carrots' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 1),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'broccoli' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 2),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT ingredient_id FROM ingredient WHERE name = 'jasmine rice' AND recipe_id = (SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry')),
 (SELECT measurement_id FROM measurement WHERE name = 'cup'),
 2);

INSERT INTO recipe_step (recipe_id, step_number, instruction) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 1, 'Cook jasmine rice according to package instructions.'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 2, 'Press and cube tofu, then pan-fry until golden.'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 3, 'In a large pot, heat coconut milk and stir in curry paste.'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 4, 'Add vegetables and simmer until tender-crisp.'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 5, 'Add tofu, soy sauce, and brown sugar.'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 6, 'Finish with lime juice and fresh basil.'),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'), 7, 'Serve hot over jasmine rice.');

-- Insert sample recipe ratings
INSERT INTO recipe_rating (recipe_id, user_id, rating_value) VALUES
-- Classic Spaghetti Carbonara ratings
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT user_id FROM user_account WHERE username = 'foodie_jane'), 5),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT user_id FROM user_account WHERE username = 'home_cook'), 4),
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT user_id FROM user_account WHERE username = 'chef_maria'), 5),

-- Vegetarian Buddha Bowl ratings
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT user_id FROM user_account WHERE username = 'foodie_jane'), 5),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT user_id FROM user_account WHERE username = 'chef_maria'), 4),
((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT user_id FROM user_account WHERE username = 'admin_chef'), 4),

-- Mediterranean Quinoa Salad ratings
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT user_id FROM user_account WHERE username = 'foodie_jane'), 4),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT user_id FROM user_account WHERE username = 'home_cook'), 5),
((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT user_id FROM user_account WHERE username = 'super_admin'), 5),

-- Spicy Thai Curry ratings
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT user_id FROM user_account WHERE username = 'chef_maria'), 5),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT user_id FROM user_account WHERE username = 'admin_chef'), 4),
((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT user_id FROM user_account WHERE username = 'home_cook'), 3);

-- Insert sample recipe comments
INSERT INTO recipe_comment (recipe_id, user_id, comment_text, created_at) VALUES
((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT user_id FROM user_account WHERE username = 'foodie_jane'),
 'This carbonara is absolutely amazing! The sauce was perfectly creamy and the pancetta adds such a wonderful flavor.',
 '2024-01-15 18:30:00'),

((SELECT recipe_id FROM recipe WHERE title = 'Vegetarian Buddha Bowl'),
 (SELECT user_id FROM user_account WHERE username = 'chef_maria'),
 'Love how customizable this bowl is. I added some extra roasted chickpeas for protein and it was delicious!',
 '2024-01-20 12:15:00'),

((SELECT recipe_id FROM recipe WHERE title = 'Mediterranean Quinoa Salad'),
 (SELECT user_id FROM user_account WHERE username = 'home_cook'),
 'Perfect light lunch option. The combination of fresh vegetables and quinoa is so refreshing.',
 '2024-01-25 13:45:00'),

((SELECT recipe_id FROM recipe WHERE title = 'Spicy Thai Curry'),
 (SELECT user_id FROM user_account WHERE username = 'admin_chef'),
 'Great balance of spices! I added a bit more coconut milk to make it creamier.',
 '2024-01-28 19:20:00'),

((SELECT recipe_id FROM recipe WHERE title = 'Classic Spaghetti Carbonara'),
 (SELECT user_id FROM user_account WHERE username = 'chef_maria'),
 'Made this for my family and they loved it! The step-by-step instructions were very helpful.',
 '2024-01-30 20:00:00');
