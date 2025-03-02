-- SQL to insert 'Non-Specific' into the recipe_diet table
-- First, delete 'Any' if it exists
DELETE FROM `recipe_diet` WHERE `name` = 'Any';

-- Then insert 'Non-Specific'
-- Using AUTO_INCREMENT to let the database assign the next available ID
INSERT INTO `recipe_diet` (`name`) 
VALUES ('Non-Specific');
