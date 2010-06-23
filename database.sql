-- phpMyAdmin SQL Dump
-- version 3.1.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 23, 2010 at 03:07 PM
-- Server version: 5.0.77
-- PHP Version: 5.2.12

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
  PRIMARY KEY  (`name`,`institution`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `code`
--

CREATE TABLE IF NOT EXISTS `code` (
  `name` varchar(255) NOT NULL default '',
  `creator` varchar(128) NOT NULL default '0',
  `url` text,
  `description` text,
  `institution` varchar(255) NOT NULL default 'example.edu',
  `public` tinyint(1) NOT NULL default '1',
  `url2` text NOT NULL,
  PRIMARY KEY  (`name`,`institution`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  PRIMARY KEY  (`code`,`user`,`institution`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
