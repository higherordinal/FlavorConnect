-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 06, 2025 at 06:46 PM
-- Server version: 8.0.41-32
-- PHP Version: 8.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swbhdnmy_db_flavorconnect`
--
CREATE DATABASE IF NOT EXISTS `swbhdnmy_db_flavorconnect` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `swbhdnmy_db_flavorconnect`;

-- --------------------------------------------------------

--
-- Table structure for table `ingredient`
--

CREATE TABLE `ingredient` (
  `ingredient_id` int UNSIGNED NOT NULL,
  `recipe_id` int UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
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
(38, 5, 'tofu'),
(48, 6, 'Arborio rice'),
(56, 6, 'Black pepper'),
(53, 6, 'Garlic'),
(49, 6, 'Mushrooms'),
(52, 6, 'Olive oil'),
(54, 6, 'Onion'),
(51, 6, 'Parmesan cheese'),
(55, 6, 'Salt'),
(50, 6, 'Vegetable broth'),
(58, 7, 'BBQ sauce'),
(66, 7, 'Black pepper'),
(64, 7, 'Brown sugar'),
(59, 7, 'Burger buns'),
(62, 7, 'Garlic powder'),
(60, 7, 'Onion slices'),
(63, 7, 'Paprika'),
(61, 7, 'Pickles'),
(57, 7, 'Pork shoulder'),
(65, 7, 'Salt'),
(72, 8, 'Black pepper'),
(76, 8, 'Butter'),
(70, 8, 'Garlic cloves'),
(68, 8, 'Lemon juice'),
(69, 8, 'Olive oil'),
(75, 8, 'Paprika'),
(73, 8, 'Rosemary'),
(71, 8, 'Salt'),
(74, 8, 'Thyme'),
(67, 8, 'Whole chicken'),
(82, 9, 'Bean sprouts'),
(80, 9, 'Broccoli'),
(79, 9, 'Carrots'),
(86, 9, 'Crushed peanuts'),
(84, 9, 'Garlic'),
(83, 9, 'Green onions'),
(85, 9, 'Lime'),
(78, 9, 'Peanut sauce'),
(77, 9, 'Rice noodles'),
(88, 9, 'Sesame oil'),
(87, 9, 'Soy sauce'),
(81, 9, 'Tofu'),
(92, 10, 'Fresh basil leaves'),
(91, 10, 'Mozzarella cheese'),
(93, 10, 'Olive oil'),
(89, 10, 'Pizza dough'),
(94, 10, 'Salt'),
(90, 10, 'Tomato sauce'),
(95, 11, 'Chickpeas'),
(101, 11, 'Cumin'),
(99, 11, 'Garlic'),
(97, 11, 'Lemon juice'),
(98, 11, 'Olive oil'),
(102, 11, 'Paprika'),
(100, 11, 'Salt'),
(96, 11, 'Tahini'),
(103, 11, 'Water'),
(104, 12, 'Black beans'),
(113, 12, 'Chili powder'),
(107, 12, 'Cilantro'),
(112, 12, 'Cumin'),
(109, 12, 'Garlic'),
(110, 12, 'Jalapeño'),
(108, 12, 'Lime'),
(115, 12, 'Olive oil'),
(106, 12, 'Red onion'),
(114, 12, 'Salt'),
(111, 12, 'Tomato'),
(105, 12, 'Tortillas'),
(121, 13, 'Bamboo shoots'),
(122, 13, 'Basil leaves'),
(119, 13, 'Bell peppers'),
(124, 13, 'Brown sugar'),
(120, 13, 'Carrots'),
(116, 13, 'Coconut milk'),
(117, 13, 'Green curry paste'),
(126, 13, 'Jasmine rice'),
(125, 13, 'Lime juice'),
(123, 13, 'Soy sauce'),
(118, 13, 'Tofu'),
(127, 14, 'All-purpose flour'),
(128, 14, 'Baking powder'),
(133, 14, 'Blueberries'),
(132, 14, 'Butter'),
(130, 14, 'Eggs'),
(134, 14, 'Maple syrup'),
(129, 14, 'Milk'),
(135, 14, 'Salt'),
(131, 14, 'Sugar'),
(142, 15, 'Black pepper'),
(137, 15, 'Butter'),
(138, 15, 'Garlic'),
(139, 15, 'Lemon juice'),
(140, 15, 'Parsley'),
(141, 15, 'Salt'),
(136, 15, 'Shrimp'),
(143, 16, 'All-purpose flour'),
(144, 16, 'Baking soda'),
(146, 16, 'Brown sugar'),
(145, 16, 'Butter'),
(151, 16, 'Chocolate chips'),
(148, 16, 'Eggs'),
(150, 16, 'Salt'),
(149, 16, 'Vanilla extract'),
(147, 16, 'White sugar'),
(152, 18, 'test'),
(153, 19, 'Test Ingredient 1'),
(154, 19, 'Test Ingredient 2'),
(155, 20, 'Test Ingredient 1'),
(156, 20, 'Test Ingredient 2'),
(157, 44, 'Test Ingredient 3'),
(166, 64, ' m,b,bm,nbmnbm'),
(165, 64, ' mn,bmvb'),
(167, 64, '.bjkhgjkhgjkgb'),
(158, 79, 'huts'),
(160, 80, 'ertwrwywyy'),
(161, 80, 'etqtyrywrtyw'),
(159, 80, 'rtrwrywywy5y'),
(162, 81, 'agtatrtarrtt'),
(163, 81, 'eatrqtqrtqt'),
(164, 82, 'garlic (minced)'),
(168, 83, '.bhjguyggjkh'),
(170, 83, '.lkhlkjhjlhhjh'),
(169, 83, 'lgkjguyg');

-- --------------------------------------------------------

--
-- Table structure for table `measurement`
--

CREATE TABLE `measurement` (
  `measurement_id` int UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
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
  `recipe_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `type_id` int UNSIGNED DEFAULT NULL,
  `style_id` int UNSIGNED DEFAULT NULL,
  `diet_id` int UNSIGNED DEFAULT NULL,
  `prep_time` int DEFAULT '0',
  `cook_time` int DEFAULT '0',
  `video_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `img_file_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alt_text` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe`
--

INSERT INTO `recipe` (`recipe_id`, `user_id`, `title`, `description`, `type_id`, `style_id`, `diet_id`, `prep_time`, `cook_time`, `video_url`, `img_file_path`, `alt_text`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 3, 'Classic Spaghetti Carbonara', 'A traditional Italian pasta dish made with eggs, cheese, pancetta, and black pepper.', 10, 2, 8, 1200, 1800, '', 'recipe_67c31a7bdca1c.jpg', 'Classic Spaghetti Carbonara', 1, '2025-02-05 14:50:27', NULL),
(2, 4, 'Breakfast Burrito', 'A hearty breakfast wrap filled with scrambled eggs, cheese, and fresh vegetables.', 1, 3, 7, 900, 600, '', '', 'Breakfast Burrito image', 0, '2025-02-05 14:50:27', NULL),
(3, 5, 'Vegetarian Buddha Bowl', 'A nourishing bowl of grains, roasted vegetables, and protein-rich toppings.', 10, 11, 1, 1800, 2400, '', 'recipe_67c31dfe1939b.jpg', 'Vegetarian Buddha Bowl image', 1, '2025-02-05 14:50:27', NULL),
(4, 2, 'Mediterranean Quinoa Salad', 'A refreshing salad with quinoa, fresh vegetables, and Mediterranean flavors.', 9, 8, 1, 1200, 1800, '', 'recipe_67c340f42b61c.jpg', 'Mediterranean Quinoa Salad image', 0, '2025-02-05 14:50:27', NULL),
(5, 3, 'Spicy Thai Curry', 'A flavorful and aromatic curry with vegetables and tofu in coconut milk.', 10, 7, 2, 1500, 2400, '', 'recipe_67c3703f6bda3.jpg', 'Spicy Thai Curry image', 1, '2025-02-05 14:50:27', NULL),
(6, 6, 'Mushroom Risotto', 'A creamy and flavorful risotto with mushrooms and parmesan.', 10, 2, 8, 1800, 2400, '', 'recipe_67c32ae999f64.jpg', 'Mushroom Risotto', 1, '2025-02-27 15:07:22', NULL),
(7, 1, 'BBQ Pulled Pork Sandwich', 'Slow-cooked pulled pork with BBQ sauce served on a bun.', 3, 1, 12, 3600, 18000, '', 'recipe_67c31e7275f33.jpg', 'BBQ Pulled Pork Sandwich image', 0, '2025-02-27 15:07:37', NULL),
(8, 2, 'Lemon Garlic Roasted Chicken', 'Juicy roasted chicken with lemon, garlic, and herbs.', 10, 9, 12, 1200, 5400, '', 'recipe_67c3408fe0d9c.jpg', 'Lemon Garlic Roasted Chicken image', 1, '2025-02-27 15:08:08', NULL),
(9, 6, 'Vegetable Pad Thai', 'A delicious Thai stir-fried noodle dish with vegetables and peanuts.', 10, 7, 2, 1500, 2400, '', 'recipe_67c37774b5b71.jpg', 'Vegetable Pad Thai image', 1, '2025-02-27 15:08:42', '2025-03-06 04:05:17'),
(10, 4, 'Classic Margherita Pizza', 'Traditional Neapolitan pizza with tomato, mozzarella, and basil.', 10, 2, 1, 2400, 900, '', 'recipe_67c376fd3102e.jpg', 'Classic Margherita Pizza image', 1, '2025-02-27 15:09:02', NULL),
(11, 4, 'Homemade Hummus', 'A smooth and creamy hummus made from chickpeas and tahini.', 9, 8, 1, 600, 0, '', 'recipe_67c377e1ee040.jpg', 'Homemade Hummus image', 0, '2025-02-27 15:09:24', NULL),
(12, 3, 'Spicy Black Bean Tacos', 'Delicious and spicy black bean tacos topped with fresh ingredients.', 10, 3, 2, 1200, 600, '', 'recipe_67c314f0569d0.jpg', 'Spicy Black Bean Tacos image', 1, '2025-02-27 15:10:03', NULL),
(13, 5, 'Thai Green Curry', 'A fragrant and spicy Thai green curry with vegetables and tofu.', 10, 7, 2, 1800, 2400, '', 'recipe_67c318a1b739c.jpg', 'Thai Green Curry image', 1, '2025-02-27 15:10:17', NULL),
(14, 2, 'Blueberry Pancakes', 'Fluffy pancakes with fresh blueberries and maple syrup.', 1, 1, 1, 900, 1200, '', 'recipe_67c283f5882ba.jpg', 'Blueberry Pancakes image', 0, '2025-02-27 15:10:17', NULL),
(15, 3, 'Garlic Butter Shrimp', 'Juicy shrimp sautéed in a garlic butter sauce with lemon.', 10, 9, 9, 900, 600, '', 'recipe_67c28600ddeda.jpg', 'Spicy Garlic Butter Shrimp recipe image', 1, '2025-02-27 15:10:49', NULL),
(16, 5, 'Chocolate Chip Cookies', 'Classic soft and chewy chocolate chip cookies.', 5, 1, 12, 900, 1200, '', 'recipe_67c285b030fe4.jpg', 'Chocolate Chip Cookies image', 0, '2025-02-27 15:10:49', '2025-03-07 03:44:18'),
(64, 6, 'test', 'test me now', 8, 3, 1, 3600, 3600, 'https://youtu.be/XLbMvY7ZxAk?si=lZ5F53iRsLtpX4Mi', 'recipe_67c7930971bb3.jpg', 'test recipe image', 0, '2025-03-05 01:43:47', '2025-03-06 06:06:33');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_comment`
--

CREATE TABLE `recipe_comment` (
  `comment_id` int UNSIGNED NOT NULL,
  `recipe_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `comment_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_comment`
--

INSERT INTO `recipe_comment` (`comment_id`, `recipe_id`, `user_id`, `comment_text`, `created_at`) VALUES
(1, 1, 5, 'This carbonara is absolutely amazing! The sauce was perfectly creamy and the pancetta adds such a wonderful flavor.', '2024-01-15 23:30:00'),
(2, 3, 3, 'Love how customizable this bowl is. I added some extra roasted chickpeas for protein and it was delicious!', '2024-01-20 17:15:00'),
(3, 4, 4, 'Perfect light lunch option. The combination of fresh vegetables and quinoa is so refreshing.', '2024-01-25 18:45:00'),
(4, 5, 2, 'Great balance of spices! I added a bit more coconut milk to make it creamier.', '2024-01-29 00:20:00'),
(5, 1, 3, 'Made this for my family and they loved it! The step-by-step instructions were very helpful.', '2024-01-31 01:00:00'),
(6, 15, 5, 'Absolutely delicious! The garlic butter sauce is perfect.', '2025-02-27 15:11:10'),
(7, 15, 6, 'Very easy to make, and the shrimp turned out juicy and flavorful.', '2025-02-27 15:11:10'),
(8, 16, 2, 'These cookies are amazing! Soft, chewy, and full of chocolate.', '2025-02-27 15:11:10'),
(9, 16, 1, 'Best homemade cookies I’ve ever had! Perfect with a glass of milk.', '2025-02-27 15:11:10'),
(10, 13, 5, 'Incredibly fragrant and flavorful! Loved the balance of spice and coconut.', '2025-02-27 15:11:10'),
(11, 13, 2, 'A great vegetarian option! Tofu soaks up the sauce beautifully.', '2025-02-27 15:11:10'),
(12, 14, 6, 'So fluffy and delicious! The blueberries make them extra special.', '2025-02-27 15:11:10'),
(13, 14, 1, 'Great recipe! I added a little vanilla and it was perfect.', '2025-02-27 15:11:10'),
(14, 6, 6, 'This risotto is incredibly creamy and full of flavor!', '2025-02-27 15:11:24'),
(15, 6, 6, 'Tasted just like a restaurant-style risotto. Loved it!', '2025-02-27 15:11:24'),
(17, 7, 3, 'Easiest BBQ recipe ever, and my whole family loved it.', '2025-02-27 15:11:24'),
(18, 8, 2, 'Juicy and flavorful! The lemon and garlic combo is perfect.', '2025-02-27 15:11:24'),
(19, 8, 5, 'Turned out absolutely amazing! I’ll be making this again.', '2025-02-27 15:11:24'),
(20, 9, 6, 'Tastes just like the one from my favorite Thai restaurant!', '2025-02-27 15:11:24'),
(21, 9, 5, 'The sauce is so good! I added extra peanuts for crunch.', '2025-02-27 15:11:24'),
(22, 10, 6, 'Authentic Italian taste! Simple but delicious.', '2025-02-27 15:11:24'),
(23, 10, 3, 'The fresh basil really makes a difference! Loved it.', '2025-02-27 15:11:24'),
(24, 11, 3, 'Creamy and so much better than store-bought!', '2025-02-27 15:11:24'),
(25, 11, 3, 'Super easy and delicious. Perfect with pita bread!', '2025-02-27 15:11:24'),
(27, 7, 6, 'Bad', '2025-03-01 15:33:13'),
(28, 68, 6, 'ok', '2025-03-05 20:19:56'),
(29, 69, 6, 'ok', '2025-03-05 21:07:15'),
(33, 64, 6, 'ok', '2025-03-06 23:13:48');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_diet`
--

CREATE TABLE `recipe_diet` (
  `diet_id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
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
(12, 'Non-Specific'),
(6, 'Paleo'),
(9, 'Pescatarian'),
(2, 'Vegan'),
(1, 'Vegetarian');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_ingredient`
--

CREATE TABLE `recipe_ingredient` (
  `recipe_ingredient_id` int UNSIGNED NOT NULL,
  `recipe_id` int UNSIGNED NOT NULL,
  `ingredient_id` int UNSIGNED NOT NULL,
  `measurement_id` int UNSIGNED NOT NULL,
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
(35, 5, 47, 1, 2.00),
(36, 6, 48, 1, 2.00),
(37, 6, 49, 3, 1.50),
(38, 6, 50, 1, 4.00),
(39, 6, 51, 1, 0.50),
(40, 6, 52, 3, 2.00),
(41, 6, 53, 2, 1.00),
(42, 6, 54, 1, 1.00),
(43, 6, 55, 2, 1.00),
(44, 6, 56, 2, 0.50),
(45, 7, 57, 1, 4.00),
(46, 7, 58, 1, 1.50),
(47, 7, 59, 1, 6.00),
(48, 7, 60, 1, 1.00),
(49, 7, 61, 1, 0.50),
(50, 7, 62, 2, 1.00),
(51, 7, 63, 2, 1.00),
(52, 7, 64, 3, 2.00),
(53, 7, 65, 2, 2.00),
(54, 7, 66, 2, 1.00),
(55, 8, 67, 1, 1.00),
(56, 8, 68, 1, 0.50),
(57, 8, 69, 1, 0.25),
(58, 8, 70, 3, 4.00),
(59, 8, 71, 2, 2.00),
(60, 8, 72, 2, 1.00),
(61, 8, 73, 3, 1.00),
(62, 8, 74, 3, 1.00),
(63, 8, 75, 2, 1.00),
(64, 8, 76, 1, 0.25),
(77, 10, 89, 1, 1.00),
(78, 10, 90, 1, 0.75),
(79, 10, 91, 1, 1.00),
(80, 10, 92, 1, 0.25),
(81, 10, 93, 3, 2.00),
(82, 10, 94, 2, 1.00),
(83, 11, 95, 1, 2.00),
(84, 11, 96, 1, 0.50),
(85, 11, 97, 1, 0.25),
(86, 11, 98, 3, 2.00),
(87, 11, 99, 2, 1.00),
(88, 11, 100, 2, 0.50),
(89, 11, 101, 2, 0.50),
(90, 11, 102, 2, 0.50),
(91, 11, 103, 1, 0.25),
(92, 12, 104, 1, 1.50),
(93, 12, 105, 1, 6.00),
(94, 12, 106, 1, 0.50),
(95, 12, 107, 1, 0.25),
(96, 12, 108, 1, 1.00),
(97, 12, 109, 2, 2.00),
(98, 12, 110, 1, 1.00),
(99, 12, 111, 1, 1.00),
(100, 12, 112, 2, 1.00),
(101, 12, 113, 2, 1.00),
(102, 12, 114, 2, 0.50),
(103, 12, 115, 3, 1.00),
(104, 13, 116, 1, 1.50),
(105, 13, 117, 3, 2.00),
(106, 13, 118, 1, 1.00),
(107, 13, 119, 1, 1.00),
(108, 14, 127, 1, 1.50),
(109, 14, 128, 2, 2.00),
(110, 14, 129, 1, 1.00),
(111, 15, 136, 1, 1.00),
(112, 15, 137, 1, 0.25),
(113, 15, 138, 2, 2.00),
(114, 15, 139, 1, 1.00),
(119, 28, 153, 1, 1.00),
(120, 29, 153, 1, 1.00),
(121, 30, 153, 1, 1.00),
(122, 30, 154, 2, 2.00),
(123, 31, 153, 1, 1.00),
(124, 31, 154, 2, 2.00),
(125, 32, 153, 1, 1.00),
(126, 32, 154, 2, 2.00),
(127, 33, 153, 1, 1.00),
(128, 33, 154, 2, 2.00),
(129, 34, 153, 1, 1.00),
(130, 34, 154, 2, 2.00),
(131, 35, 153, 1, 1.00),
(132, 35, 154, 2, 2.00),
(133, 36, 153, 1, 1.00),
(134, 36, 154, 2, 2.00),
(135, 38, 153, 1, 1.00),
(136, 38, 154, 2, 2.00),
(137, 39, 153, 1, 1.00),
(138, 39, 154, 2, 2.00),
(139, 40, 153, 1, 1.00),
(140, 40, 154, 2, 2.00),
(141, 41, 153, 1, 1.00),
(142, 41, 154, 2, 2.00),
(143, 42, 153, 1, 1.00),
(144, 42, 154, 2, 2.00),
(145, 43, 153, 1, 1.00),
(146, 43, 154, 2, 2.00),
(147, 44, 153, 1, 1.00),
(148, 44, 154, 2, 2.00),
(149, 44, 157, 3, 3.00),
(153, 45, 153, 1, 1.00),
(154, 45, 154, 2, 2.00),
(155, 45, 157, 3, 3.00),
(159, 46, 153, 1, 1.00),
(160, 46, 154, 2, 2.00),
(161, 46, 157, 3, 3.00),
(162, 56, 153, 1, 2.00),
(163, 56, 154, 2, 3.00),
(164, 57, 153, 1, 2.00),
(165, 57, 154, 3, 1.50),
(166, 57, 157, 2, 0.50),
(191, 66, 17, 4, 1.00),
(192, 66, 144, 1, 1.00),
(193, 66, 76, 7, 1.00),
(194, 67, 17, 2, 1.00),
(195, 67, 2, 2, 1.00),
(196, 67, 9, 2, 1.00),
(203, 79, 77, 7, 1.00),
(204, 79, 158, 3, 1.00),
(205, 79, 76, 4, 1.00),
(206, 80, 159, 3, 1.00),
(207, 80, 160, 8, 1.00),
(208, 80, 161, 9, 1.00),
(209, 81, 17, 7, 1.00),
(210, 81, 162, 2, 1.00),
(211, 81, 163, 7, 1.00),
(212, 68, 17, 9, 1.00),
(213, 68, 144, 9, 1.00),
(214, 68, 76, 9, 1.00),
(218, 82, 17, 2, 1.00),
(219, 82, 76, 2, 1.00),
(220, 82, 164, 3, 1.00),
(221, 69, 17, 9, 1.00),
(222, 69, 144, 1, 1.00),
(223, 69, 76, 9, 1.00),
(236, 9, 77, 1, 8.00),
(237, 9, 78, 1, 0.50),
(238, 9, 41, 1, 1.00),
(239, 9, 42, 1, 1.00),
(240, 9, 38, 1, 0.50),
(241, 9, 82, 1, 1.00),
(242, 9, 83, 1, 0.50),
(243, 9, 53, 2, 2.00),
(244, 9, 85, 1, 1.00),
(245, 9, 86, 1, 0.25),
(246, 9, 43, 3, 2.00),
(247, 9, 88, 3, 1.00),
(248, 64, 165, 7, 1.00),
(249, 64, 166, 4, 3.00),
(250, 64, 167, 6, 7.00),
(251, 83, 168, 3, 1.00),
(252, 83, 169, 5, 1.00),
(253, 83, 170, 8, 1.00),
(258, 16, 127, 1, 2.50),
(259, 16, 144, 2, 1.00),
(260, 16, 76, 1, 1.00),
(261, 16, 44, 1, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_rating`
--

CREATE TABLE `recipe_rating` (
  `rating_id` int NOT NULL,
  `recipe_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `rating_value` tinyint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
(12, 5, 4, 3),
(13, 15, 1, 5),
(14, 15, 2, 4),
(15, 16, 5, 5),
(16, 16, 4, 5),
(17, 13, 4, 5),
(18, 13, 2, 4),
(19, 14, 5, 5),
(20, 14, 6, 4),
(21, 6, 6, 5),
(22, 6, 4, 4),
(23, 7, 3, 5),
(24, 7, 1, 5),
(25, 8, 4, 5),
(26, 8, 1, 4),
(27, 9, 4, 5),
(28, 9, 2, 4),
(29, 10, 1, 5),
(30, 10, 1, 5),
(31, 11, 2, 5),
(32, 11, 4, 4),
(34, 7, 6, 4),
(35, 68, 6, 3),
(36, 69, 6, 3),
(46, 75, 6, 4),
(47, 64, 6, 3);

-- --------------------------------------------------------

--
-- Table structure for table `recipe_step`
--

CREATE TABLE `recipe_step` (
  `step_id` int UNSIGNED NOT NULL,
  `recipe_id` int UNSIGNED NOT NULL,
  `step_number` int NOT NULL,
  `instruction` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
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
(36, 5, 7, 'Serve hot over jasmine rice.'),
(37, 6, 1, 'Heat olive oil in a pan and sauté onions and garlic until fragrant.'),
(38, 6, 2, 'Add mushrooms and cook until softened.'),
(39, 6, 3, 'Stir in Arborio rice and toast for 1-2 minutes.'),
(40, 6, 4, 'Gradually add vegetable broth, stirring continuously.'),
(41, 6, 5, 'Continue cooking and stirring until the rice is tender and creamy.'),
(42, 6, 6, 'Stir in Parmesan cheese, season with salt and black pepper.'),
(43, 6, 7, 'Remove from heat and let sit for 2 minutes before serving.'),
(44, 7, 1, 'Season pork shoulder with garlic powder, paprika, brown sugar, salt, and black pepper.'),
(45, 7, 2, 'Place pork shoulder in a slow cooker and cook on low for 8-10 hours until tender.'),
(46, 7, 3, 'Shred the pork using two forks.'),
(47, 7, 4, 'Mix shredded pork with BBQ sauce and cook for another 30 minutes.'),
(48, 7, 5, 'Toast the burger buns.'),
(49, 7, 6, 'Assemble the sandwich by placing BBQ pork on buns, topping with onion slices and pickles.'),
(50, 7, 7, 'Serve warm and enjoy.'),
(51, 8, 1, 'Preheat oven to 375°F (190°C).'),
(52, 8, 2, 'Pat dry the chicken with paper towels.'),
(53, 8, 3, 'Rub the chicken with lemon juice and olive oil.'),
(54, 8, 4, 'Season generously with salt, black pepper, paprika, rosemary, and thyme.'),
(55, 8, 5, 'Stuff the cavity with garlic cloves and additional herbs.'),
(56, 8, 6, 'Place the chicken in a roasting pan and spread butter over the skin.'),
(57, 8, 7, 'Roast for about 1.5 hours, basting occasionally with pan juices.'),
(58, 8, 8, 'Check internal temperature (should be 165°F at thickest part).'),
(59, 8, 9, 'Let the chicken rest for 10-15 minutes before carving.'),
(60, 8, 10, 'Serve warm and enjoy.'),
(71, 10, 1, 'Preheat oven to 500°F (260°C) and place a pizza stone inside if available.'),
(72, 10, 2, 'Roll out the pizza dough on a floured surface to desired thickness.'),
(73, 10, 3, 'Spread tomato sauce evenly over the pizza dough, leaving a small border.'),
(74, 10, 4, 'Tear mozzarella into small pieces and distribute them over the sauce.'),
(75, 10, 5, 'Drizzle olive oil over the top and sprinkle with a pinch of salt.'),
(76, 10, 6, 'Bake in the preheated oven for 7-10 minutes, or until the crust is golden and cheese is bubbly.'),
(77, 10, 7, 'Remove from the oven and immediately add fresh basil leaves.'),
(78, 10, 8, 'Let cool slightly before slicing and serving.'),
(79, 11, 1, 'Drain and rinse the chickpeas.'),
(80, 11, 2, 'In a food processor, blend tahini and lemon juice for 1 minute.'),
(81, 11, 3, 'Add garlic, olive oil, salt, cumin, and paprika, and blend again.'),
(82, 11, 4, 'Slowly add chickpeas, blending continuously.'),
(83, 11, 5, 'Pour in water gradually and blend until smooth.'),
(84, 11, 6, 'Taste and adjust seasoning if needed.'),
(85, 11, 7, 'Transfer to a serving bowl and drizzle with additional olive oil.'),
(86, 11, 8, 'Sprinkle with paprika and serve with pita bread or vegetables.'),
(87, 12, 1, 'Heat olive oil in a pan over medium heat.'),
(88, 12, 2, 'Add chopped red onion, garlic, and jalapeño, and sauté until fragrant.'),
(89, 12, 3, 'Stir in black beans, cumin, chili powder, and salt. Cook for 5 minutes.'),
(90, 12, 4, 'Mash some of the beans with the back of a spoon for texture.'),
(91, 12, 5, 'Warm the tortillas in a dry pan or microwave.'),
(92, 12, 6, 'Fill each tortilla with black bean mixture and top with diced tomato and cilantro.'),
(93, 12, 7, 'Squeeze fresh lime juice over the tacos before serving.'),
(94, 13, 1, 'Heat coconut milk in a pan and add green curry paste.'),
(95, 13, 2, 'Add chopped vegetables and cook until slightly tender.'),
(96, 13, 3, 'Add tofu and simmer for 5 minutes.'),
(97, 13, 4, 'Stir in soy sauce, brown sugar, and lime juice.'),
(98, 13, 5, 'Serve hot over jasmine rice with fresh basil leaves.'),
(99, 14, 1, 'In a bowl, whisk together flour, baking powder, and salt.'),
(100, 14, 2, 'In a separate bowl, whisk eggs, milk, and melted butter.'),
(101, 14, 3, 'Combine wet and dry ingredients, then fold in blueberries.'),
(102, 14, 4, 'Heat a pan and pour in batter to form pancakes.'),
(103, 14, 5, 'Flip when bubbles form and cook until golden brown.'),
(104, 14, 6, 'Serve warm with maple syrup.'),
(105, 15, 1, 'Melt butter in a pan over medium heat.'),
(106, 15, 2, 'Add minced garlic and cook until fragrant.'),
(107, 15, 3, 'Add shrimp, salt, and black pepper, and cook until pink.'),
(108, 15, 4, 'Drizzle with lemon juice and garnish with parsley.'),
(109, 15, 5, 'Serve warm.'),
(122, 44, 1, 'Step 1: This is the first instruction'),
(123, 44, 2, 'Step 2: This is the second instruction'),
(124, 44, 3, 'Step 3: This is the third instruction'),
(128, 45, 1, 'Step 1: This is the first instruction'),
(129, 45, 2, 'Step 2: This is the second instruction'),
(130, 45, 3, 'Step 3: This is the third instruction'),
(134, 46, 1, 'Step 1: This is the first instruction'),
(135, 46, 2, 'Step 2: This is the second instruction'),
(136, 46, 3, 'Step 3: This is the third instruction'),
(160, 66, 1, 'sadfgbshs'),
(161, 66, 2, 'afgshsjshs'),
(162, 67, 1, 'etqwyw5yw5ywy5'),
(163, 67, 2, 'wtywyw6ywwywyw5t'),
(168, 81, 1, 'erqetrqtrwretw'),
(169, 81, 2, 'qrtqrtqrtqtqrtq'),
(170, 68, 1, 'aefnq;;wrt'),
(171, 68, 2, 'qrtywywtywty'),
(174, 82, 1, 'erfrgwywetyw'),
(175, 82, 2, 'argahytshsh'),
(176, 69, 1, ',bjhgjhgb'),
(177, 69, 2, ',jhghkgfkhjgv'),
(188, 9, 1, 'Cook rice noodles according to package instructions and drain.'),
(189, 9, 2, 'Heat sesame oil in a pan over medium heat.'),
(190, 9, 3, 'Add garlic and cook until fragrant.'),
(191, 9, 4, 'Add tofu and cook until golden brown.'),
(192, 9, 5, 'Stir in carrots, broccoli, and bean sprouts, and cook for 2-3 minutes.'),
(193, 9, 6, 'Push vegetables to one side of the pan and add beaten eggs to scramble.'),
(194, 9, 7, 'Combine everything and add the cooked noodles.'),
(195, 9, 8, 'Pour peanut sauce and soy sauce over the noodles and stir to coat evenly.'),
(196, 9, 9, 'Remove from heat and add lime juice, green onions, and crushed peanuts.'),
(197, 9, 10, 'Serve hot and enjoy.'),
(198, 64, 1, '.mjbjklhjbkbkln'),
(199, 64, 2, 'bnvjkgvkvjkhgj'),
(200, 83, 1, 'nnkjnkjlhkljhklj'),
(201, 83, 2, 'nn,mnkjhkljhklhk'),
(211, 16, 1, 'Preheat oven to 350°F (175°C).'),
(212, 16, 2, 'In a bowl, mix flour, baking soda, and salt.'),
(213, 16, 3, 'In another bowl, beat butter, brown sugar, and white sugar until creamy.'),
(214, 16, 4, 'Add eggs and vanilla extract to the mixture.'),
(215, 16, 5, 'Gradually mix in dry ingredients.'),
(216, 16, 6, 'Stir in chocolate chips.'),
(217, 16, 7, 'Drop dough by spoonfuls onto a baking sheet.'),
(218, 16, 8, 'Bake for 10-12 minutes until golden brown.'),
(219, 16, 9, 'Cool on a wire rack and serve.');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_style`
--

CREATE TABLE `recipe_style` (
  `style_id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_style`
--

INSERT INTO `recipe_style` (`style_id`, `name`) VALUES
(1, 'American'),
(11, 'Asian'),
(4, 'Chinese'),
(12, 'Ethiopian'),
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
  `type_id` int NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
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
  `user_id` int UNSIGNED NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `first_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `user_level` enum('s','a','u') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'u',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_account`
--

INSERT INTO `user_account` (`user_id`, `username`, `first_name`, `last_name`, `email`, `password_hash`, `user_level`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'hcvaughn', 'Henry', 'Vaughn', 'henrycvaughn@students.abtech.edu', 'Divided4union', 'a', 1, '2025-03-04 15:46:35', '2025-03-06 04:24:19'),
(2, 'admin_chef', 'Michael', 'Brown', 'michael@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'a', 1, '2025-03-04 15:46:35', NULL),
(3, 'chef_maria', 'Maria', 'Garcia', 'maria@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u', 1, '2025-03-04 15:46:35', NULL),
(4, 'home_cook', 'David', 'Wilson', 'david@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u', 1, '2025-03-04 15:46:35', NULL),
(5, 'foodie_jane', 'Jane', 'Smith', 'jane@example.com', '$2y$10$8sA2N5Sx/1zMG6RN2ZC1Y.oqhPE0U6Y3MxEJdA9WwR8wMYsDAF.Ym', 'u', 1, '2025-03-04 15:46:35', '2025-03-05 16:34:15'),
(6, 'test_user', 'Test', 'User', 'test@user.com', '$2y$10$wcCE89SUfrazauK1wxuc8uidXkEpa.qy094gy9b4SyMBEJmd0mL4K', 'a', 1, '2025-03-04 15:46:35', NULL),
(7, 'registertest', 'Register', 'Tester', 'register@test.com', '$2y$10$fAcyiRDl2QmHvU7q0WR2R.SRlvVfZs4LBfu3NAq2yR9n7.L6b7Esa', 'u', 1, '2025-03-04 15:52:53', '2025-03-06 03:27:43'),
(8, 'jhandcock', 'John', 'Handcock', 'jc@handcock.com', '$2y$10$3IvdWtB9YOlAHO5e7f4gR.JKg5yPxeli5KogHReoCBIEKKOaQjI16', 'u', 1, '2025-03-05 23:56:19', '2025-03-06 00:02:53'),
(9, 'mWOwTPMvxHgJ', 'EIWeFchZDIzDR', 'ztjfSCLXdHN', 'oneidacurtisth25@gmail.com', '$2y$10$6iAKVpG16bvWGd83sl1ixOXMJDTKcoA8VTrSxKsh0it/h/vrKVfAe', 'u', 1, '2025-03-06 07:08:37', NULL),
(10, 'vaughndmd', 'Henry', 'Vaughn', 'vaughndmd@gmail.com', '$2y$10$8XVhf.aUHGaPHSuWfLdZCufWNgK7BIdC.mLdph/9e.puFJuLri4Sy', 'u', 1, '2025-03-06 23:49:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_favorite`
--

CREATE TABLE `user_favorite` (
  `user_id` int UNSIGNED NOT NULL,
  `recipe_id` int UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_favorite`
--

INSERT INTO `user_favorite` (`user_id`, `recipe_id`, `created_at`) VALUES
(6, 6, '2025-03-07 00:30:12'),
(6, 9, '2025-03-06 23:09:42'),
(6, 10, '2025-03-06 23:09:43'),
(6, 12, '2025-03-06 23:26:16'),
(6, 13, '2025-03-06 23:11:53'),
(6, 14, '2025-03-06 23:09:40'),
(6, 15, '2025-03-06 22:57:39'),
(10, 10, '2025-03-07 00:13:53');

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
  MODIFY `ingredient_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT for table `measurement`
--
ALTER TABLE `measurement`
  MODIFY `measurement_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `recipe`
--
ALTER TABLE `recipe`
  MODIFY `recipe_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `recipe_comment`
--
ALTER TABLE `recipe_comment`
  MODIFY `comment_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `recipe_diet`
--
ALTER TABLE `recipe_diet`
  MODIFY `diet_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `recipe_ingredient`
--
ALTER TABLE `recipe_ingredient`
  MODIFY `recipe_ingredient_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `recipe_rating`
--
ALTER TABLE `recipe_rating`
  MODIFY `rating_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `recipe_step`
--
ALTER TABLE `recipe_step`
  MODIFY `step_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT for table `recipe_style`
--
ALTER TABLE `recipe_style`
  MODIFY `style_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `recipe_type`
--
ALTER TABLE `recipe_type`
  MODIFY `type_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `user_account`
--
ALTER TABLE `user_account`
  MODIFY `user_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;