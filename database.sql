-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 06, 2010 at 03:46 PM
-- Server version: 5.0.77
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
  `name` varchar(255) NOT NULL default '',
  `code` varchar(255) NOT NULL default '',
  `institution` varchar(255) NOT NULL default 'example.edu',
  `updated` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`name`,`institution`),
  KEY `updated` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `code`
--

CREATE TABLE IF NOT EXISTS `code` (
  `name` varchar(255) NOT NULL default '',
  `url` text,
  `description` text,
  `institution` varchar(255) NOT NULL default 'example.edu',
  `public` tinyint(1) NOT NULL default '1',
  `url2` text NOT NULL,
  `updated` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`name`,`institution`),
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
  `timestamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `institution` varchar(255) NOT NULL,
  `url` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) NOT NULL auto_increment,
  `tstamp` timestamp NOT NULL default CURRENT_TIMESTAMP,
  `code` varchar(255) NOT NULL,
  `alias` varchar(255) default NULL,
  `institution` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `user_display_name` varchar(255) NOT NULL,
  `request` text,
  `referer` text,
  PRIMARY KEY  (`id`),
  KEY `tstamp` (`tstamp`),
  KEY `code` (`code`),
  KEY `alias` (`alias`),
  KEY `institution` (`institution`),
  KEY `user_id` (`user_id`),
  KEY `user_display_name` (`user_display_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `name` varchar(128) NOT NULL default '0',
  `notify` tinyint(2) NOT NULL default '1',
  PRIMARY KEY  (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_to_code`
--

CREATE TABLE IF NOT EXISTS `user_to_code` (
  `code` varchar(255) NOT NULL default '',
  `user` varchar(128) NOT NULL default '0',
  `institution` varchar(255) NOT NULL default 'example.edu',
  `updated` timestamp NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`code`,`user`,`institution`),
  KEY `updated` (`updated`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
