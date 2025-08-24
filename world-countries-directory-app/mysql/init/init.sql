DROP DATABASE IF EXISTS world_db;

CREATE DATABASE world_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE world_db;

CREATE TABLE countries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_name VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(255) NOT NULL UNIQUE,
    iso_alpha2 CHAR(2) NOT NULL UNIQUE,
    iso_alpha3 CHAR(3) NOT NULL UNIQUE,
    iso_numeric CHAR(3) NOT NULL UNIQUE,
    population BIGINT UNSIGNED NOT NULL,
    square BIGINT UNSIGNED NOT NULL
);

USE world_db;
INSERT INTO countries (short_name, full_name, iso_alpha2, iso_alpha3, iso_numeric, population, square) 
VALUES
('Россия','Российская Федерация','RU','RUS','643',146150789,17125191),
('США','Соединённые Штаты Америки','US','USA','840',331893745,9833520),
('Франция','Французская Республика','FR','FRA','250',67391582,551695),
('Германия','Федеративная Республика Германия','DE','DEU','276',83240525,357386),
('Япония','Япония','JP','JPN','392',125502000,377975);
