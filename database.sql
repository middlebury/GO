-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 27, 2011 at 11:57 AM
-- Server version: 5.1.56
-- PHP Version: 5.2.12

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `go`
--

-- --------------------------------------------------------

--
-- Table structure for table `alias`
--

CREATE TABLE IF NOT EXISTS `alias` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `code` varchar(255) NOT NULL DEFAULT '',
  `institution` varchar(255) NOT NULL DEFAULT 'middlebury.edu',
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`,`institution`),
  KEY `updated` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `code`
--

CREATE TABLE IF NOT EXISTS `code` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `url` text,
  `description` text,
  `institution` varchar(255) NOT NULL DEFAULT 'middlebury.edu',
  `public` tinyint(1) NOT NULL DEFAULT '1',
  `url2` text,
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`,`institution`),
  KEY `updated` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `flag`
--

CREATE TABLE IF NOT EXISTS `flag` (
  `code` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `ipaddress` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `institution` varchar(255) NOT NULL,
  `url` text NOT NULL,
  `comment` text NOT NULL,
  `completed` varchar(255) NOT NULL DEFAULT '0',
  `completed_on` datetime NOT NULL,
  `notes` varchar(2000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tstamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `code` varchar(255) NOT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `institution` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `user_display_name` varchar(255) NOT NULL,
  `request` text,
  `referer` text,
  PRIMARY KEY (`id`),
  KEY `tstamp` (`tstamp`),
  KEY `code` (`code`),
  KEY `alias` (`alias`),
  KEY `institution` (`institution`),
  KEY `user_id` (`user_id`),
  KEY `user_display_name` (`user_display_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7570 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `name` varchar(128) NOT NULL DEFAULT '0',
  `notify` tinyint(2) NOT NULL DEFAULT '1',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_to_code`
--

CREATE TABLE IF NOT EXISTS `user_to_code` (
  `code` varchar(255) NOT NULL DEFAULT '',
  `user` varchar(128) NOT NULL DEFAULT '0',
  `institution` varchar(255) NOT NULL DEFAULT 'middlebury.edu',
  `updated` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`code`,`user`,`institution`),
  KEY `updated` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
