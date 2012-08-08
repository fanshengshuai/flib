drop database  if exists test_www;
create database test_www default charset 'utf8';

CREATE USER 'test_www'@'localhost' IDENTIFIED BY 'qWbAJDtzrJqydMyv';
GRANT USAGE ON * . * TO 'test_www'@'localhost' IDENTIFIED BY 'qWbAJDtzrJqydMyv' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
GRANT ALL PRIVILEGES ON `test_www` . * TO 'test_www'@'localhost' WITH GRANT OPTION ;
