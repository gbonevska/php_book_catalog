-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Хост: 127.0.0.1
-- Време на генериране: 
-- Версия на сървъра: 5.5.27
-- Версия на PHP: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- БД: `ggbuchen_katalog_knigi`
--

-- --------------------------------------------------------

--
-- Структура на таблица `authors`
--

CREATE TABLE IF NOT EXISTS `authors` (
  `author_id` int(11) NOT NULL AUTO_INCREMENT,
  `author_name` varchar(250) NOT NULL,
  PRIMARY KEY (`author_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=32 ;

--
-- Ссхема на данните от таблица `authors`
--

INSERT INTO `authors` (`author_id`, `author_name`) VALUES
(29, 'Емил Зола'),
(30, 'Майкъл Х. Ернандес'),
(31, 'Брус Екел');

-- --------------------------------------------------------

--
-- Структура на таблица `books`
--

CREATE TABLE IF NOT EXISTS `books` (
  `book_id` int(11) NOT NULL AUTO_INCREMENT,
  `book_title` varchar(250) NOT NULL,
  `notes` varchar(1000) DEFAULT NULL,
  PRIMARY KEY (`book_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=30 ;

--
-- Ссхема на данните от таблица `books`
--

INSERT INTO `books` (`book_id`, `book_title`, `notes`) VALUES
(20, 'Нова книга с колекция', NULL),
(21, 'Книга тест 6', 'Тест пореден забележка'),
(22, 'Проектиране на Бази от Данни', NULL),
(26, 'Да мислим на Java 2', NULL),
(27, 'Да мислим на Java', NULL),
(28, 'Нова книга без колекция', NULL),
(29, 'Нова книга without collection', NULL);

-- --------------------------------------------------------

--
-- Структура на таблица `books_authors`
--

CREATE TABLE IF NOT EXISTS `books_authors` (
  `book_id` int(11) NOT NULL,
  `author_id` int(11) NOT NULL,
  `collection_id` int(11) DEFAULT NULL,
  KEY `book_id` (`book_id`),
  KEY `author_id` (`author_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Ссхема на данните от таблица `books_authors`
--

INSERT INTO `books_authors` (`book_id`, `author_id`, `collection_id`) VALUES
(26, 31, NULL),
(22, 30, 3),
(20, 29, 2),
(21, 29, 2),
(27, 31, NULL),
(28, 29, NULL),
(29, 29, NULL);

-- --------------------------------------------------------

--
-- Структура на таблица `collections`
--

CREATE TABLE IF NOT EXISTS `collections` (
  `collection_id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_name` varchar(250) NOT NULL,
  PRIMARY KEY (`collection_id`),
  UNIQUE KEY `collection_id` (`collection_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Ссхема на данните от таблица `collections`
--

INSERT INTO `collections` (`collection_id`, `collection_name`) VALUES
(2, 'Любовни романи с имена на жени'),
(3, 'Компютърна литература');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
