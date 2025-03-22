CREATE DATABASE IF NOT EXISTS `flavorconnect` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `flavorconnect`;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS 'user'@'%' IDENTIFIED BY '@connect4Establish';
GRANT ALL PRIVILEGES ON flavorconnect.* TO 'user'@'%';
FLUSH PRIVILEGES;
