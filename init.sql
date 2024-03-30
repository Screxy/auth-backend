CREATE DATABASE IF NOT EXISTS auth;
USE auth;
CREATE TABLE IF NOT EXISTS user (
    id INT auto_increment,
    primary key (id),
    email varchar(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    access_token varchar(255)
);
