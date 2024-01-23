CREATE DATABASE IF NOT EXISTS `app`;
CREATE DATABASE IF NOT EXISTS `app_test`;
CREATE DATABASE IF NOT EXISTS `app_test1`;
CREATE DATABASE IF NOT EXISTS `app_test2`;
CREATE DATABASE IF NOT EXISTS `app_test3`;
CREATE DATABASE IF NOT EXISTS `app_test4`;
CREATE DATABASE IF NOT EXISTS `app_test5`;

CREATE USER IF NOT EXISTS 'user'@'%' IDENTIFIED BY 'pass';
GRANT ALL PRIVILEGES ON *.* TO 'user'@'%';

FLUSH PRIVILEGES;