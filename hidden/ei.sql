-- phpMyAdmin SQL Dump
-- version 3.4.8deb1.natty~ppa.1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 21-02-2012 a las 15:36:39
-- Versión del servidor: 5.1.54
-- Versión de PHP: 5.3.8-1~ppa3~natty

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `ei`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `_auth`
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `_auth`
--

INSERT INTO `_auth` (`user_id`, `auth_access`, `auth_insert`, `auth_edit`, `auth_null`, `auth_delete`, `auth_search`, `auth_ranks`, `auth_print`, `auth_log`) VALUES
(2, 1, 1, 1, 1, 1, 1, 1, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `_config`
--

CREATE TABLE IF NOT EXISTS `_config` (
  `config_name` varchar(100) NOT NULL,
  `config_value` varchar(100) NOT NULL,
  PRIMARY KEY (`config_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `_config`
--

INSERT INTO `_config` (`config_name`, `config_value`) VALUES
('cookie_name', 'ei'),
('ip_check', '3'),
('root', '/ei/'),
('saddress', 'http://localhost'),
('sesion_length', '3600'),
('session_gc', '3600'),
('session_last_gc', '1329854754');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `_constancia`
--

CREATE TABLE IF NOT EXISTS `_constancia` (
  `c_exe` int(11) NOT NULL,
  `c_null` tinyint(1) NOT NULL,
  `c_date` int(11) NOT NULL,
  `c_nit` varchar(25) NOT NULL,
  `c_text` text NOT NULL,
  `c_np` tinyint(1) NOT NULL,
  PRIMARY KEY (`c_exe`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `_constancia`
--

INSERT INTO `_constancia` (`c_exe`, `c_null`, `c_date`, `c_nit`, `c_text`, `c_np`) VALUES
(1, 0, 1329804000, '31585698', 'DW', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `_factura`
--

CREATE TABLE IF NOT EXISTS `_factura` (
  `f_id` int(11) NOT NULL AUTO_INCREMENT,
  `f_exe` int(11) NOT NULL,
  `f_serie` varchar(20) NOT NULL,
  `f_fact` varchar(20) NOT NULL,
  `f_date` int(11) NOT NULL,
  `f_total` double NOT NULL,
  PRIMARY KEY (`f_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `_factura`
--

INSERT INTO `_factura` (`f_id`, `f_exe`, `f_serie`, `f_fact`, `f_date`, `f_total`) VALUES
(1, 1, '1', '11', 1329804000, 560);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `_log`
--

CREATE TABLE IF NOT EXISTS `_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_user_id` mediumint(5) NOT NULL,
  `log_date` int(11) NOT NULL,
  `log_exe` int(11) NOT NULL,
  `log_action` varchar(255) NOT NULL,
  PRIMARY KEY (`log_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Volcado de datos para la tabla `_log`
--

INSERT INTO `_log` (`log_id`, `log_user_id`, `log_date`, `log_exe`, `log_action`) VALUES
(1, 2, 1329855827, 0, 'pi.31585698.0'),
(2, 2, 1329857495, 1, 'i'),
(3, 2, 1329857513, 1, 'i.11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `_prov`
--

CREATE TABLE IF NOT EXISTS `_prov` (
  `p_id` int(11) NOT NULL AUTO_INCREMENT,
  `p_nit` varchar(25) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_sf` varchar(20) NOT NULL,
  PRIMARY KEY (`p_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `_prov`
--

INSERT INTO `_prov` (`p_id`, `p_nit`, `p_name`, `p_sf`) VALUES
(1, '31585698', 'LEONEL AZURDIA', '0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `_sessions`
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

--
-- Volcado de datos para la tabla `_sessions`
--

INSERT INTO `_sessions` (`session_id`, `session_user_id`, `session_last_visit`, `session_start`, `session_time`, `session_ip`, `session_page`) VALUES
('3ff309530ebbadc49d268bd9fd319890', 1, 1329855739, 1329855739, 1329855739, '127.0.0.1', '/ei/login/'),
('55b3171463068a25c4449723365ce87f', 2, 1329855719, 1329855739, 1329859737, '127.0.0.1', '/ei/users/2/'),
('fce89bae1a6450077dfb8f7aea6b783b', 1, 1329855119, 1329855119, 1329855310, '127.0.0.1', '/ei/login/');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `_users`
--

CREATE TABLE IF NOT EXISTS `_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_adm` tinyint(1) NOT NULL,
  `username` varchar(25) NOT NULL,
  `user_password` varchar(128) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_lastvisit` int(11) NOT NULL,
  `user_rank_min` int(11) NOT NULL,
  `user_rank_max` int(11) NOT NULL,
  `user_return_insert` tinyint(1) NOT NULL,
  `user_print_copies` tinyint(3) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Volcado de datos para la tabla `_users`
--

INSERT INTO `_users` (`user_id`, `user_adm`, `username`, `user_password`, `user_email`, `user_lastvisit`, `user_rank_min`, `user_rank_max`, `user_return_insert`, `user_print_copies`) VALUES
(1, 0, 'guest', '', '', 0, 0, 0, 0, 0),
(2, 1, 'ntc', '9d660d1c010cd5154eba57cf90006b598b57e6a5401687c079cc6da6f0385680dc2b305fe128bc7c28e219069fc87b8df2f2da6c1a2ca508b65e855a6869a3a3', 'info@nopticon.com', 1329850858, 0, 0, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
