-- phpMyAdmin SQL Dump
-- version 3.3.7deb6
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 01-02-2012 a las 08:11:05
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
-- Estructura de tabla para la tabla `Academias`
--

CREATE TABLE IF NOT EXISTS `Academias` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Nombre` varchar(100) NOT NULL,
  `Maestro` int(7) unsigned zerofill DEFAULT NULL,
  `Subida` tinyint(1) NOT NULL DEFAULT '0',
  `Materias` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Alumnos`
--

CREATE TABLE IF NOT EXISTS `Alumnos` (
  `Codigo` char(9) NOT NULL,
  `Carrera` char(5) NOT NULL,
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
-- Estructura de tabla para la tabla `Alumnos_Aplicadores`
--

CREATE TABLE IF NOT EXISTS `Alumnos_Aplicadores` (
  `Id` int(11) NOT NULL,
  `Alumno` char(9) NOT NULL,
  KEY `Alumno` (`Alumno`),
  KEY `Id` (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Alumnos_Aplicadores`:
--   `Alumno`
--       `Alumnos` -> `Codigo`
--   `Id`
--       `Salones_Aplicadores` -> `Id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Calificaciones`
--

CREATE TABLE IF NOT EXISTS `Calificaciones` (
  `Alumno` char(9) NOT NULL,
  `Nrc` int(5) unsigned zerofill NOT NULL,
  `Tipo` int(11) NOT NULL,
  `Valor` int(11) DEFAULT NULL,
  PRIMARY KEY (`Alumno`,`Nrc`,`Tipo`),
  KEY `Calificacion` (`Alumno`,`Nrc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Calificaciones`:
--   `Alumno`
--       `Alumnos` -> `Codigo`
--   `Nrc`
--       `Secciones` -> `Nrc`
--   `Tipo`
--       `Evaluaciones` -> `Id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Carreras`
--

CREATE TABLE IF NOT EXISTS `Carreras` (
  `Clave` char(5) NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  PRIMARY KEY (`Clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Evaluaciones`
--

CREATE TABLE IF NOT EXISTS `Evaluaciones` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Grupo` int(11) NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  `Exclusiva` tinyint(1) NOT NULL,
  `Estado` enum('open','closed','time') NOT NULL DEFAULT 'open',
  `Apertura` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Cierre` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- RELACIONES PARA LA TABLA `Evaluaciones`:
--   `Grupo`
--       `Grupos_Evaluaciones` -> `Id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Grupos`
--

CREATE TABLE IF NOT EXISTS `Grupos` (
  `Alumno` char(9) NOT NULL,
  `Nrc` int(5) unsigned zerofill NOT NULL,
  PRIMARY KEY (`Alumno`,`Nrc`)
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
-- Estructura de tabla para la tabla `Grupos_Evaluaciones`
--

CREATE TABLE IF NOT EXISTS `Grupos_Evaluaciones` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Descripcion` varchar(30) NOT NULL,
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Maestros`
--

CREATE TABLE IF NOT EXISTS `Maestros` (
  `Codigo` int(7) unsigned zerofill NOT NULL,
  `Nombre` varchar(30) NOT NULL,
  `Apellido` varchar(70) NOT NULL,
  `Correo` varchar(100) NOT NULL,
  `Flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`Codigo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Materias`
--

CREATE TABLE IF NOT EXISTS `Materias` (
  `Clave` char(5) NOT NULL,
  `Descripcion` varchar(100) NOT NULL,
  `Academia` int(11) DEFAULT NULL,
  PRIMARY KEY (`Clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Materias`:
--   `Academia`
--       `Academias` -> `Id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Permisos`
--

CREATE TABLE IF NOT EXISTS `Permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aed_usuarios` tinyint(1) NOT NULL DEFAULT '0',
  `crear_grupos` tinyint(1) NOT NULL DEFAULT '0',
  `asignar_aplicadores` tinyint(1) NOT NULL DEFAULT '0',
  `grupos_globales` tinyint(1) NOT NULL DEFAULT '1',
  `crear_materias` tinyint(1) NOT NULL DEFAULT '0',
  `admin_carreras` tinyint(1) NOT NULL DEFAULT '0',
  `admin_evaluaciones` tinyint(1) NOT NULL DEFAULT '0',
  `admin_academias` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Porcentajes`
--

CREATE TABLE IF NOT EXISTS `Porcentajes` (
  `Clave` char(5) NOT NULL,
  `Tipo` int(11) NOT NULL,
  `Ponderacion` int(11) NOT NULL,
  UNIQUE KEY `Materia` (`Clave`,`Tipo`),
  KEY `Clave` (`Clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Porcentajes`:
--   `Clave`
--       `Materias` -> `Clave`
--   `Tipo`
--       `Evaluaciones` -> `Id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Promedios`
--

CREATE TABLE IF NOT EXISTS `Promedios` (
  `Nrc` int(5) unsigned zerofill NOT NULL,
  `Tipo` int(11) NOT NULL,
  `Promedio` decimal(5,2) NOT NULL,
  PRIMARY KEY (`Nrc`,`Tipo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Promedios`:
--   `Nrc`
--       `Secciones` -> `Nrc`
--   `Tipo`
--       `Evaluaciones` -> `Id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Salones_Aplicadores`
--

CREATE TABLE IF NOT EXISTS `Salones_Aplicadores` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Materia` char(5) NOT NULL,
  `Tipo` int(11) NOT NULL,
  `Salon` char(20) NOT NULL,
  `FechaHora` datetime NOT NULL,
  `Maestro` int(7) unsigned zerofill DEFAULT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `Salon` (`Salon`,`FechaHora`),
  KEY `Materia` (`Materia`),
  KEY `Evaluacion` (`Materia`,`Tipo`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- RELACIONES PARA LA TABLA `Salones_Aplicadores`:
--   `Maestro`
--       `Maestros` -> `Codigo`
--   `Materia`
--       `Materias` -> `Clave`
--   `Tipo`
--       `Evaluaciones` -> `Id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Secciones`
--

CREATE TABLE IF NOT EXISTS `Secciones` (
  `Nrc` int(5) unsigned zerofill NOT NULL,
  `Materia` char(5) NOT NULL,
  `Maestro` int(7) unsigned zerofill NOT NULL,
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
  `Codigo` char(9) NOT NULL,
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
  `Codigo` int(7) unsigned zerofill NOT NULL,
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

-- --------------------------------------------------------

--
-- Volcar la base de datos para la tabla `Carreras`
--

INSERT INTO `Carreras` (`Clave`, `Descripcion`) VALUES
('BIM', 'Ingeniería en Biomédica'),
('CEL', 'Ingeniería en Comunicaciones y Electrónica'),
('CIV', 'Ingeniería Civil'),
('COM', 'Ingeniería en Computación'),
('FIS', 'Licenciatura en Física'),
('IND', 'Ingeniería Industrial'),
('INF', 'Licenciatura en Informática'),
('IQU', 'Ingeniería Química'),
('MAT', 'Licenciatura en Matemáticas'),
('MEL', 'Ingeniería Mecánica Eléctrica'),
('QFB', 'Licenciatura en Químico Farmacobiólogo'),
('QUI', 'Licenciatura en Química'),
('TOP', 'Ingeniería Topográfica');

--
-- Volcar la base de datos para la tabla `Grupos_Evaluaciones`
--

INSERT INTO `Grupos_Evaluaciones` (`Id`, `Descripcion`) VALUES
(1, 'Ordinario'),
(2, 'Extraordinario'),
(3, 'Cursos de verano');

--
-- Volcar la base de datos para la tabla `Evaluaciones`
--

INSERT INTO `Evaluaciones` (`Id`, `Grupo`, `Descripcion`, `Exclusiva`, `Apertura`, `Cierre`) VALUES
(1, 1, 'Departamental 1', 0, NOW(), NOW()),
(2, 1, 'Departamental 2', 0, NOW(), NOW()),
(3, 1, 'Departamental 3', 0, NOW(), NOW()),
(4, 1, 'Departamental 4', 0, NOW(), NOW()),
(5, 1, 'Departamental 5', 0, NOW(), NOW()),
(10, 1, 'Puntos del maestro', 1, NOW(), NOW()),
(11, 1, 'Moodle', 1, NOW(), NOW()),
(12, 1, 'Proymoodle', 1, NOW(), NOW()),
(100, 2, 'Extraordinario', 0, NOW(), NOW());

