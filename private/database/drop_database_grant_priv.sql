-- SQL to drop the database if it exists
DROP DATABASE IF EXISTS `flavorconnect`;

-- Create user and grant privileges
CREATE USER IF NOT EXISTS 'hcvaughn'@'%' IDENTIFIED BY '@connect4Establish';
GRANT ALL PRIVILEGES ON flavorconnect.* TO 'hcvaughn'@'%';
FLUSH PRIVILEGES;
