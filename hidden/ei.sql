-- phpMyAdmin SQL Dump
-- version 3.4.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 21, 2012 at 01:17 PM
-- Server version: 5.1.44
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ei`
--

-- --------------------------------------------------------

--
-- Table structure for table `_auth`
--

CREATE TABLE IF NOT EXISTS `_auth` (
  `user_id` mediumint(5) NOT NULL AUTO_INCREMENT,
  `auth_access` smallint(1) NOT NULL,
  `auth_insert` smallint(6) NOT NULL,
  `auth_edit` smallint(6) NOT NULL,
  `auth_null` smallint(6) NOT NULL,
  `auth_delete` smallint(6) NOT NULL,
  `auth_search` smallint(6) NOT NULL,
  `auth_ranks` smallint(6) NOT NULL,
  `auth_print` smallint(6) NOT NULL,
  `auth_log` smallint(6) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `_config`
--

CREATE TABLE IF NOT EXISTS `_config` (
  `config_name` varchar(100) NOT NULL,
  `config_value` varchar(100) NOT NULL,
  PRIMARY KEY (`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `_constancia`
--

CREATE TABLE IF NOT EXISTS `_constancia` (
  `c_exe` int(11) NOT NULL,
  `c_null` tinyint(1) NOT NULL,
  `c_date` int(11) NOT NULL,
  `c_nit` varchar(25) NOT NULL,
  `c_text` text NOT NULL,
  PRIMARY KEY (`c_exe`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `_factura`
--

CREATE TABLE IF NOT EXISTS `_factura` (
  `f_id` int(11) NOT NULL AUTO_INCREMENT,
  `f_exe` int(11) NOT NULL,
  `f_serie` varchar(20) NOT NULL,
  `f_fact` varchar(20) NOT NULL,
  `f_date` int(11) NOT NULL,
  `f_total` double NOT NULL,
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `_log`
--

CREATE TABLE IF NOT EXISTS `_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_user_id` mediumint(5) NOT NULL,
  `log_date` int(11) NOT NULL,
  `log_exe` int(11) NOT NULL,
  `log_action` varchar(255) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `_prov`
--

CREATE TABLE IF NOT EXISTS `_prov` (
  `p_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_nit` varchar(25) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `_sessions`
--

CREATE TABLE IF NOT EXISTS `_sessions` (
  `session_id` varchar(50) NOT NULL,
  `session_user_id` mediumint(5) NOT NULL,
  `session_last_visit` int(11) NOT NULL,
  `session_start` int(11) NOT NULL,
  `session_time` int(11) NOT NULL,
  `session_ip` varchar(40) NOT NULL,
  `session_page` varchar(255) NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `_users`
--

CREATE TABLE IF NOT EXISTS `_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_adm` tinyint(1) NOT NULL,
  `user_name` varchar(25) NOT NULL,
  `user_password` varchar(128) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_lastvisit` int(11) NOT NULL,
  `user_rank_min` int(11) NOT NULL,
  `user_rank_max` int(11) NOT NULL,
  `user_return_insert` tinyint(1) NOT NULL,
  `user_print_copies` tinyint(3) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
