-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Feb 05, 2025 at 04:11 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `flavorconnect`
--
DROP DATABASE IF EXISTS flavorconnect;
CREATE DATABASE flavorconnect;
USE flavorconnect;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS 'hcvaughn'@'%' IDENTIFIED BY '@connect4Establish';
GRANT ALL PRIVILEGES ON flavorconnect.* TO 'hcvaughn'@'%';
FLUSH PRIVILEGES;

-- --------------------------------------------------------

--
-- Table structure for table `ingredient`
--

CREATE TABLE `ingredient` (
  `ingredient_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredient`
--

INSERT INTO `ingredient` (`ingredient_id`, `recipe_id`, `name`) VALUES
(5, 1, 'black pepper'),
(2, 1, 'eggs'),
(4, 1, 'pancetta'),
(3, 1, 'pecorino romano cheese'),
(6, 1, 'salt'),
(1, 1, 'spaghetti'),
(12, 2, 'avocado'),
(9, 2, 'black beans'),
(10, 2, 'cheddar cheese'),
(8, 2, 'eggs'),
(7, 2, 'large tortillas'),
(14, 2, 'pepper'),
(11, 2, 'salsa'),
(13, 2, 'salt'),
(19, 3, 'avocado'),
(17, 3, 'chickpeas'),
(18, 3, 'kale'),
(21, 3, 'lemon juice'),
(22, 3, 'olive oil'),
(24, 3, 'pepper'),
(15, 3, 'quinoa'),
(23, 3, 'salt'),
(16, 3, 'sweet potato'),
(20, 3, 'tahini'),
(27, 4, 'cherry tomatoes'),
(26, 4, 'cucumber'),
(30, 4, 'feta cheese'),
(33, 4, 'fresh parsley'),
(29, 4, 'kalamata olives'),
(32, 4, 'lemon juice'),
(31, 4, 'olive oil'),
(35, 4, 'pepper'),
(25, 4, 'quinoa'),
(28, 4, 'red onion'),
(34, 4, 'salt'),
(40, 5, 'bamboo shoots'),
(46, 5, 'basil leaves'),
(39, 5, 'bell peppers'),
(42, 5, 'broccoli'),
(44, 5, 'brown sugar'),
(41, 5, 'carrots'),
(36, 5, 'coconut milk'),
(47, 5, 'jasmine rice'),
(45, 5, 'lime juice'),
(37, 5, 'red curry paste'),
(43, 5, 'soy sauce'),
(38, 5, 'tofu');

-- --------------------------------------------------------

--
-- Table structure for table `measurement`
--

CREATE TABLE `measurement` (
  `measurement_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `measurement`
--

INSERT INTO `measurement` (`measurement_id`, `name`) VALUES
(1, 'cup'),
(9, 'dash'),
(4, 'gram'),
(5, 'kilogram'),
(10, 'liter'),
(11, 'milliliter'),
(6, 'ounce'),
(8, 'pinch'),
(7, 'pound'),
(3, 'tablespoon'),
(2, 'teaspoon');

-- --------------------------------------------------------

--
-- Table structure for table `recipe`
--

CREATE TABLE `recipe` (
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `type_id` int(10) UNSIGNED DEFAULT NULL,
  `style_id` int(10) UNSIGNED DEFAULT NULL,
  `diet_id` int(10) UNSIGNED DEFAULT NULL,
  `prep_time` int(11) DEFAULT 0,
  `cook_time` int(11) DEFAULT 0,
  `video_url` varchar(255) DEFAULT NULL,
  `img_file_path` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe`
--

INSERT INTO `recipe` (`recipe_id`, `user_id`, `title`, `description`, `type_id`, `style_id`, `diet_id`, `prep_time`, `cook_time`, `video_url`, `img_file_path`, `alt_text`, `is_featured`, `created_at`) VALUES
(1, 3, 'Classic Spaghetti Carbonara', 'A traditional Italian pasta dish made with eggs, cheese, pancetta, and black pepper.', 10, 2, 8, 1200, 1800, NULL, NULL, NULL, 1, '2025-02-05 14:50:27'),
(2, 4, 'Breakfast Burrito', 'A hearty breakfast wrap filled with scrambled eggs, cheese, and fresh vegetables.', 1, 3, 7, 900, 600, NULL, NULL, NULL, 0, '2025-02-05 14:50:27'),
(3, 5, 'Vegetarian Buddha Bowl', 'A nourishing bowl of grains, roasted vegetables, and protein-rich toppings.', 10, 11, 1, 1800, 2400, NULL, NULL, NULL, 1, '2025-02-05 14:50:27'),
(4, 2, 'Mediterranean Quinoa Salad', 'A refreshing salad with quinoa, fresh vegetables, and Mediterranean flavors.', 9, 8, 1, 1200, 1800, NULL, NULL, NULL, 0, '2025-02-05 14:50:27'),
(5, 3, 'Spicy Thai Curry', 'A flavorful and aromatic curry with vegetables and tofu in coconut milk.', 10, 7, 2, 1500, 2400, NULL, NULL, NULL, 1, '2025-02-05 14:50:27');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_comment`
--

CREATE TABLE `recipe_comment` (
  `comment_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_comment`
--

INSERT INTO `recipe_comment` (`comment_id`, `recipe_id`, `user_id`, `comment_text`, `created_at`) VALUES
(1, 1, 5, 'This carbonara is absolutely amazing! The sauce was perfectly creamy and the pancetta adds such a wonderful flavor.', '2024-01-15 23:30:00'),
(2, 3, 3, 'Love how customizable this bowl is. I added some extra roasted chickpeas for protein and it was delicious!', '2024-01-20 17:15:00'),
(3, 4, 4, 'Perfect light lunch option. The combination of fresh vegetables and quinoa is so refreshing.', '2024-01-25 18:45:00'),
(4, 5, 2, 'Great balance of spices! I added a bit more coconut milk to make it creamier.', '2024-01-29 00:20:00'),
(5, 1, 3, 'Made this for my family and they loved it! The step-by-step instructions were very helpful.', '2024-01-31 01:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_diet`
--

CREATE TABLE `recipe_diet` (
  `diet_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_diet`
--

INSERT INTO `recipe_diet` (`diet_id`, `name`) VALUES
(4, 'Dairy-Free'),
(3, 'Gluten-Free'),
(10, 'Halal'),
(5, 'Keto'),
(7, 'Low-Carb'),
(8, 'Low-Fat'),
(6, 'Paleo'),
(9, 'Pescatarian'),
(2, 'Vegan'),
(1, 'Vegetarian');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ingredient`
--

CREATE TABLE `recipe_ingredient` (
  `recipe_ingredient_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `ingredient_id` int(10) UNSIGNED NOT NULL,
  `measurement_id` int(10) UNSIGNED NOT NULL,
  `quantity` decimal(10,2) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_ingredient`
--

INSERT INTO `recipe_ingredient` (`recipe_ingredient_id`, `recipe_id`, `ingredient_id`, `measurement_id`, `quantity`) VALUES
(1, 1, 1, 7, 1.00),
(2, 1, 2, 1, 4.00),
(3, 1, 3, 1, 1.00),
(4, 1, 4, 6, 8.00),
(5, 1, 5, 2, 2.00),
(6, 1, 6, 2, 1.00),
(7, 2, 7, 1, 4.00),
(8, 2, 8, 1, 6.00),
(9, 2, 9, 1, 1.00),
(10, 2, 10, 1, 1.00),
(11, 2, 11, 1, 0.50),
(12, 2, 12, 1, 1.00),
(13, 3, 15, 1, 1.00),
(14, 3, 16, 1, 2.00),
(15, 3, 17, 1, 1.00),
(16, 3, 18, 1, 2.00),
(17, 3, 19, 1, 1.00),
(18, 3, 20, 3, 2.00),
(19, 3, 21, 3, 1.00),
(20, 4, 25, 1, 1.50),
(21, 4, 26, 1, 1.00),
(22, 4, 27, 1, 1.00),
(23, 4, 28, 1, 0.50),
(24, 4, 29, 1, 0.50),
(25, 4, 30, 1, 0.75),
(26, 4, 31, 3, 3.00),
(27, 4, 32, 3, 2.00),
(28, 5, 36, 1, 2.00),
(29, 5, 37, 3, 2.00),
(30, 5, 38, 1, 2.00),
(31, 5, 39, 1, 1.00),
(32, 5, 40, 1, 1.00),
(33, 5, 41, 1, 1.00),
(34, 5, 42, 1, 2.00),
(35, 5, 47, 1, 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_rating`
--

CREATE TABLE `recipe_rating` (
  `rating_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `rating_value` tinyint(4) NOT NULL CHECK (`rating_value` between 1 and 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_rating`
--

INSERT INTO `recipe_rating` (`rating_id`, `recipe_id`, `user_id`, `rating_value`) VALUES
(1, 1, 5, 5),
(2, 1, 4, 4),
(3, 1, 3, 5),
(4, 3, 5, 5),
(5, 3, 3, 4),
(6, 3, 2, 4),
(7, 4, 5, 4),
(8, 4, 4, 5),
(9, 4, 1, 5),
(10, 5, 3, 5),
(11, 5, 2, 4),
(12, 5, 4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_step`
--

CREATE TABLE `recipe_step` (
  `step_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `step_number` int(11) NOT NULL,
  `instruction` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_step`
--

INSERT INTO `recipe_step` (`step_id`, `recipe_id`, `step_number`, `instruction`) VALUES
(1, 1, 1, 'Bring a large pot of salted water to boil for the pasta.'),
(2, 1, 2, 'While water is heating, cut the pancetta into small cubes.'),
(3, 1, 3, 'In a bowl, whisk together eggs, grated pecorino cheese, and black pepper.'),
(4, 1, 4, 'Cook spaghetti in the boiling water according to package instructions.'),
(5, 1, 5, 'While pasta cooks, sauté pancetta in a large pan until crispy.'),
(6, 1, 6, 'Reserve 1 cup pasta water, then drain pasta.'),
(7, 1, 7, 'Working quickly, add hot pasta to pancetta, then mix in egg mixture, stirring constantly.'),
(8, 1, 8, 'Add pasta water as needed to create a creamy sauce. Serve immediately.'),
(9, 2, 1, 'Warm the tortillas in a large skillet.'),
(10, 2, 2, 'Scramble the eggs with salt and pepper until just set.'),
(11, 2, 3, 'Heat the black beans in a small saucepan.'),
(12, 2, 4, 'Slice the avocado.'),
(13, 2, 5, 'Layer each tortilla with eggs, beans, cheese, avocado, and salsa.'),
(14, 2, 6, 'Roll up tightly, tucking in the sides as you go.'),
(15, 2, 7, 'Optional: Return to skillet and brown on all sides.'),
(16, 3, 1, 'Cook quinoa according to package instructions.'),
(17, 3, 2, 'Roast sweet potato cubes with olive oil, salt, and pepper.'),
(18, 3, 3, 'Season and roast chickpeas until crispy.'),
(19, 3, 4, 'Massage kale with olive oil and salt.'),
(20, 3, 5, 'Make dressing by whisking tahini, lemon juice, and water.'),
(21, 3, 6, 'Assemble bowls with quinoa base, topped with vegetables.'),
(22, 3, 7, 'Add sliced avocado and drizzle with tahini dressing.'),
(23, 4, 1, 'Cook quinoa according to package instructions, let cool.'),
(24, 4, 2, 'Dice cucumber and quarter cherry tomatoes.'),
(25, 4, 3, 'Finely slice red onion and chop parsley.'),
(26, 4, 4, 'In a large bowl, combine cooled quinoa with all chopped vegetables.'),
(27, 4, 5, 'Add olives and crumbled feta cheese.'),
(28, 4, 6, 'Whisk together olive oil, lemon juice, salt, and pepper.'),
(29, 4, 7, 'Pour dressing over salad and toss to combine. Serve chilled.'),
(30, 5, 1, 'Cook jasmine rice according to package instructions.'),
(31, 5, 2, 'Press and cube tofu, then pan-fry until golden.'),
(32, 5, 3, 'In a large pot, heat coconut milk and stir in curry paste.'),
(33, 5, 4, 'Add vegetables and simmer until tender-crisp.'),
(34, 5, 5, 'Add tofu, soy sauce, and brown sugar.'),
(35, 5, 6, 'Finish with lime juice and fresh basil.'),
(36, 5, 7, 'Serve hot over jasmine rice.');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_style`
--

CREATE TABLE `recipe_style` (
  `style_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_style`
--

INSERT INTO `recipe_style` (`style_id`, `name`) VALUES
(1, 'American'),
(11, 'Asian'),
(4, 'Chinese'),
(9, 'French'),
(5, 'Indian'),
(2, 'Italian'),
(6, 'Japanese'),
(10, 'Korean'),
(8, 'Mediterranean'),
(3, 'Mexican'),
(7, 'Thai');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_type`
--

CREATE TABLE `recipe_type` (
  `type_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_type`
--

INSERT INTO `recipe_type` (`type_id`, `name`) VALUES
(4, 'Appetizer'),
(7, 'Beverage'),
(1, 'Breakfast'),
(5, 'Dessert'),
(3, 'Dinner'),
(2, 'Lunch'),
(10, 'Main Course'),
(9, 'Salad'),
(6, 'Snack'),
(8, 'Soup');

-- --------------------------------------------------------

--
-- Table structure for table `user_account`
--

CREATE TABLE `user_account` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `user_level` enum('s','a','u') NOT NULL DEFAULT 'u',
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_id`, `username`, `first_name`, `last_name`, `email`, `password_hash`, `user_level`, `is_active`) VALUES
(1, 'super_admin', 'Henry', 'Vaughn', 'henrycvaughn@students.abtech.edu', 'Divided4union', 's', 1),
(2, 'admin_chef', 'Michael', 'Brown', 'michael@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'a', 1),
(3, 'chef_maria', 'Maria', 'Garcia', 'maria@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u', 1),
(4, 'home_cook', 'David', 'Wilson', 'david@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u', 1),
(5, 'foodie_jane', 'Jane', 'Smith', 'jane@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_favorite`
--

CREATE TABLE `user_favorite` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `recipe_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ingredient`
--
ALTER TABLE `ingredient`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD UNIQUE KEY `recipe_id` (`recipe_id`,`name`),
  ADD KEY `idx_recipe_id` (`recipe_id`),
  ADD KEY `idx_name` (`name`);

--
-- Indexes for table `measurement`
--
ALTER TABLE `measurement`
  ADD PRIMARY KEY (`measurement_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `recipe`
--
ALTER TABLE `recipe`
  ADD PRIMARY KEY (`recipe_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `style_id` (`style_id`),
  ADD KEY `diet_id` (`diet_id`),
  ADD KEY `idx_recipe_title` (`title`),
  ADD KEY `idx_recipe_featured` (`is_featured`),
  ADD KEY `idx_recipe_type_diet_style` (`type_id`,`diet_id`,`style_id`);

--
-- Indexes for table `recipe_comment`
--
ALTER TABLE `recipe_comment`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_comment_recipe` (`recipe_id`);

--
-- Indexes for table `recipe_diet`
--
ALTER TABLE `recipe_diet`
  ADD PRIMARY KEY (`diet_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_recipe_diet_name` (`name`);

--
-- Indexes for table `recipe_ingredient`
--
ALTER TABLE `recipe_ingredient`
  ADD PRIMARY KEY (`recipe_ingredient_id`),
  ADD KEY `measurement_id` (`measurement_id`),
  ADD KEY `idx_recipe_id` (`recipe_id`),
  ADD KEY `idx_ingredient_id` (`ingredient_id`);

--
-- Indexes for table `recipe_rating`
--
ALTER TABLE `recipe_rating`
  ADD PRIMARY KEY (`rating_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_rating_recipe_user` (`recipe_id`,`user_id`);

--
-- Indexes for table `recipe_step`
--
ALTER TABLE `recipe_step`
  ADD PRIMARY KEY (`step_id`),
  ADD KEY `idx_step_recipe` (`recipe_id`);

--
-- Indexes for table `recipe_style`
--
ALTER TABLE `recipe_style`
  ADD PRIMARY KEY (`style_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_recipe_style_name` (`name`);

--
-- Indexes for table `recipe_type`
--
ALTER TABLE `recipe_type`
  ADD PRIMARY KEY (`type_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_recipe_type_name` (`name`);

--
-- Indexes for table `user_account`
--
ALTER TABLE `user_account`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_username` (`username`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_user_is_active` (`is_active`),
  ADD KEY `idx_user_last_name` (`last_name`),
  ADD KEY `idx_user_level_lastname` (`user_level`,`last_name`);
ALTER TABLE `user_account` ADD FULLTEXT KEY `idx_user_name` (`first_name`,`last_name`);

--
-- Indexes for table `user_favorite`
--
ALTER TABLE `user_favorite`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ingredient`
--
ALTER TABLE `ingredient`
  MODIFY `ingredient_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `measurement`
--
ALTER TABLE `measurement`
  MODIFY `measurement_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `recipe`
--
ALTER TABLE `recipe`
  MODIFY `recipe_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `recipe_comment`
--
ALTER TABLE `recipe_comment`
  MODIFY `comment_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `recipe_diet`
--
ALTER TABLE `recipe_diet`
  MODIFY `diet_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `recipe_ingredient`
--
ALTER TABLE `recipe_ingredient`
  MODIFY `recipe_ingredient_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `recipe_rating`
--
ALTER TABLE `recipe_rating`
  MODIFY `rating_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `recipe_step`
--
ALTER TABLE `recipe_step`
  MODIFY `step_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `recipe_style`
--
ALTER TABLE `recipe_style`
  MODIFY `style_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `recipe_type`
--
ALTER TABLE `recipe_type`
  MODIFY `type_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ingredient`
--
ALTER TABLE `ingredient`
  ADD CONSTRAINT `ingredient_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipe` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe`
--
ALTER TABLE `recipe`
  ADD CONSTRAINT `recipe_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `recipe_type` (`type_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recipe_ibfk_3` FOREIGN KEY (`style_id`) REFERENCES `recipe_style` (`style_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `recipe_ibfk_4` FOREIGN KEY (`diet_id`) REFERENCES `recipe_diet` (`diet_id`) ON DELETE SET NULL;

--
-- Constraints for table `recipe_comment`
--
ALTER TABLE `recipe_comment`
  ADD CONSTRAINT `recipe_comment_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipe` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_comment_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_ingredient`
--
ALTER TABLE `recipe_ingredient`
  ADD CONSTRAINT `recipe_ingredient_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipe` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredient_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredient` (`ingredient_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredient_ibfk_3` FOREIGN KEY (`measurement_id`) REFERENCES `measurement` (`measurement_id`);

--
-- Constraints for table `recipe_rating`
--
ALTER TABLE `recipe_rating`
  ADD CONSTRAINT `recipe_rating_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipe` (`recipe_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_rating_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_step`
--
ALTER TABLE `recipe_step`
  ADD CONSTRAINT `recipe_step_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipe` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_favorite`
--
ALTER TABLE `user_favorite`
  ADD CONSTRAINT `user_favorite_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_account` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorite_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipe` (`recipe_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
