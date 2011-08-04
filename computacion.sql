-- phpMyAdmin SQL Dump
-- version 3.3.7deb5
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 04-08-2011 a las 03:17:27
-- Versión del servidor: 5.1.49
-- Versión de PHP: 5.3.3-7+squeeze1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `computacion`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Alumnos`
--

CREATE TABLE IF NOT EXISTS `Alumnos` (
  `codigo` int(9) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `carrera` char(3) DEFAULT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Carreras`
--

CREATE TABLE IF NOT EXISTS `Carreras` (
  `clave` char(3) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Grupos`
--

CREATE TABLE IF NOT EXISTS `Grupos` (
  `materia` char(5) NOT NULL,
  `seccion` char(4) NOT NULL,
  `maestro` int(7) NOT NULL,
  `alumno` int(9) NOT NULL,
  `1erdepa` tinyint(4) DEFAULT NULL,
  `2dodepa` tinyint(4) DEFAULT NULL,
  `puntos` tinyint(4) DEFAULT NULL,
  `ordinario` tinyint(4) DEFAULT NULL,
  `extra` tinyint(4) DEFAULT NULL,
  UNIQUE KEY `materia` (`materia`,`alumno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Maestros`
--

CREATE TABLE IF NOT EXISTS `Maestros` (
  `codigo` int(7) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  PRIMARY KEY (`codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Materias`
--

CREATE TABLE IF NOT EXISTS `Materias` (
  `clave` char(5) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
