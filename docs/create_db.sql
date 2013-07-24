#drop database if exists flib_demo;
create database flib_demo default charset 'utf8';

CREATE USER 'flib_demo'@'localhost' IDENTIFIED BY 'DYvaCsrQQEzXACBK';
GRANT USAGE ON * . * TO 'flib_demo'@'localhost' IDENTIFIED BY 'DYvaCsrQQEzXACBK' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;
GRANT ALL PRIVILEGES ON `flib_demo` . * TO 'flib_demo'@'localhost' WITH GRANT OPTION ;
