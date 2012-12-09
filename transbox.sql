-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Dec 09, 2012 at 01:23 AM
-- Server version: 5.5.28
-- PHP Version: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `transbox`
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `torrents`
--

CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `duplicate` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `hash` varchar(40) NOT NULL,
  `path` varchar(512) NOT NULL,
  `size` bigint(20) NOT NULL,
  `percent` double NOT NULL DEFAULT '0',
  `txed` bigint(20) NOT NULL DEFAULT '0',
  `rxed` bigint(20) NOT NULL DEFAULT '0',
  `stopped` tinyint(1) NOT NULL DEFAULT '0',
  `added_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_hash` (`uid`,`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL,
  `password` varchar(32) NOT NULL,
  `level` int(11) NOT NULL DEFAULT '2',
  `ds_limit` bigint(20) NOT NULL,
  `ds_current` bigint(20) NOT NULL DEFAULT '0',
  `xfer_limit` bigint(20) NOT NULL,
  `rx_limit` bigint(20) NOT NULL,
  `rx_current` bigint(20) NOT NULL DEFAULT '0',
  `tx_limit` bigint(20) NOT NULL,
  `tx_current` bigint(20) NOT NULL DEFAULT '0',
  `rx_speed` int(11) NOT NULL,
  `tx_speed` int(11) NOT NULL,
  `ratio` double NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
