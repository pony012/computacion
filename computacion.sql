-- phpMyAdmin SQL Dump
-- version 3.3.7deb6
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 07-09-2011 a las 03:04:01
-- Versión del servidor: 5.1.49
-- Versión de PHP: 5.3.3-7+squeeze3

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
  `Codigo` int(9) NOT NULL,
  `Carrera` char(3) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Flag` tinyint(1) NOT NULL,
  PRIMARY KEY (`Codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Alumnos`:
--   `Carrera`
--       `Carreras` -> `Clave`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Aplicadores`
--

CREATE TABLE IF NOT EXISTS `Aplicadores` (
  `Alumno` int(9) NOT NULL,
  `Salon` char(6) NOT NULL,
  `FechaHora` datetime NOT NULL,
  `Tipo` enum('1','2','Extra') NOT NULL,
  `Maestro` int(7) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Aplicadores`:
--   `Alumno`
--       `Alumnos` -> `Codigo`
--   `Maestro`
--       `Maestros` -> `Codigo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Carreras`
--

CREATE TABLE IF NOT EXISTS `Carreras` (
  `Clave` char(3) NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`Clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Grupos`
--

CREATE TABLE IF NOT EXISTS `Grupos` (
  `Alumno` int(9) NOT NULL,
  `Nrc` int(5) NOT NULL,
  `1erdepa` int(11) DEFAULT NULL,
  `2dodepa` int(11) DEFAULT NULL,
  `Puntos` int(11) DEFAULT NULL,
  `Promedio` int(11) NOT NULL,
  `Extra` int(11) DEFAULT NULL,
  UNIQUE KEY `Alumno` (`Alumno`,`Nrc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Grupos`:
--   `Alumno`
--       `Alumnos` -> `Codigo`
--   `Nrc`
--       `Secciones` -> `Nrc`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Maestros`
--

CREATE TABLE IF NOT EXISTS `Maestros` (
  `Codigo` int(7) NOT NULL,
  `Nombre` varchar(30) NOT NULL,
  `Apellido` varchar(70) NOT NULL,
  `Correo` varchar(100) NOT NULL,
  `Flag` tinyint(1) NOT NULL,
  PRIMARY KEY (`Codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Materias`
--

CREATE TABLE IF NOT EXISTS `Materias` (
  `Clave` char(5) NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  `Depa1` tinyint(1) NOT NULL DEFAULT '1',
  `Depa2` tinyint(1) NOT NULL DEFAULT '1',
  `Puntos` tinyint(1) NOT NULL DEFAULT '1',
  `Porcentaje_Depa1` int(11) DEFAULT NULL,
  `Porcentaje_Depa2` int(11) DEFAULT NULL,
  `Porcentaje_Puntos` int(11) DEFAULT NULL,
  PRIMARY KEY (`Clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Permisos`
--

CREATE TABLE IF NOT EXISTS `Permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aed_usuarios` tinyint(1) NOT NULL,
  `crear_grupos` tinyint(1) NOT NULL,
  `asignar_aplicadores` tinyint(1) NOT NULL,
  `grupos_globales` tinyint(1) NOT NULL,
  `crear_materias` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Secciones`
--

CREATE TABLE IF NOT EXISTS `Secciones` (
  `Nrc` int(5) NOT NULL,
  `Materia` char(5) NOT NULL,
  `Maestro` int(7) NOT NULL,
  `Seccion` char(3) NOT NULL,
  PRIMARY KEY (`Nrc`),
  UNIQUE KEY `Materia` (`Materia`,`Seccion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Secciones`:
--   `Maestro`
--       `Maestros` -> `Codigo`
--   `Materia`
--       `Materias` -> `Clave`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Sesiones_Alumnos`
--

CREATE TABLE IF NOT EXISTS `Sesiones_Alumnos` (
  `Codigo` int(9) NOT NULL,
  `Pass` char(32) NOT NULL,
  `Permisos` int(11) NOT NULL,
  `Activo` tinyint(1) NOT NULL,
  PRIMARY KEY (`Codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Sesiones_Alumnos`:
--   `Codigo`
--       `Alumnos` -> `Codigo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Sesiones_Maestros`
--

CREATE TABLE IF NOT EXISTS `Sesiones_Maestros` (
  `Codigo` int(7) NOT NULL,
  `Pass` char(32) NOT NULL,
  `Permisos` int(11) NOT NULL,
  `Activo` tinyint(1) NOT NULL,
  PRIMARY KEY (`Codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Sesiones_Maestros`:
--   `Codigo`
--       `Maestros` -> `Codigo`
--   `Permisos`
--       `Permisos` -> `id`
--
