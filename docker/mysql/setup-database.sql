CREATE DATABASE `factorio-item-browser` COLLATE 'utf8_general_ci';
CREATE USER 'factorio-item-browser'@'%' IDENTIFIED BY 'factorio-item-browser';
GRANT ALL PRIVILEGES ON `factorio-item-browser`.* TO 'factorio-item-browser'@'%';

FLUSH PRIVILEGES;