-- phpMyAdmin SQL Dump
-- version 3.3.7deb6
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 10-11-2011 a las 23:07:27
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
  `Codigo` char(9) NOT NULL,
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
  `Alumno` char(9) NOT NULL,
  `Materia` char(5) NOT NULL,
  `Salon` char(20) NOT NULL,
  `FechaHora` datetime NOT NULL,
  `Tipo` int(11) NOT NULL,
  `Maestro` int(7) DEFAULT NULL,
  PRIMARY KEY (`Alumno`,`Materia`,`Tipo`),
  KEY `Alumno` (`Alumno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Aplicadores`:
--   `Alumno`
--       `Alumnos` -> `Codigo`
--   `Maestro`
--       `Maestros` -> `Codigo`
--   `Tipo`
--       `Evaluaciones` -> `Id`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Calificaciones`
--

CREATE TABLE IF NOT EXISTS `Calificaciones` (
  `Alumno` char(9) NOT NULL,
  `Nrc` int(5) NOT NULL,
  `Tipo` int(11) NOT NULL,
  `Valor` int(11) DEFAULT NULL,
  PRIMARY KEY (`Alumno`,`Nrc`,`Tipo`),
  KEY `Calificacion` (`Alumno`,`Nrc`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- RELACIONES PARA LA TABLA `Calificaciones`:
--   `Alumno`
--       `Grupos` -> `Alumno`
--   `Nrc`
--       `Grupos` -> `Nrc`
--   `Tipo`
--       `Evaluaciones` -> `Id`
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
-- Estructura de tabla para la tabla `Evaluaciones`
--

CREATE TABLE IF NOT EXISTS `Evaluaciones` (
  `Id` int(11) NOT NULL AUTO_INCREMENT,
  `Descripcion` varchar(100) NOT NULL,
  `Exclusiva` tinyint(1) NOT NULL,
  `Apertura` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Cierre` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`Id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Grupos`
--

CREATE TABLE IF NOT EXISTS `Grupos` (
  `Alumno` char(9) NOT NULL,
  `Nrc` int(5) NOT NULL,
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
-- Estructura de tabla para la tabla `Maestros`
--

CREATE TABLE IF NOT EXISTS `Maestros` (
  `Codigo` int(7) NOT NULL,
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
  PRIMARY KEY (`Clave`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Permisos`
--

CREATE TABLE IF NOT EXISTS `Permisos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `aed_usuarios` tinyint(1) NOT NULL DEFAULT '0',
  `crear_grupos` tinyint(1) NOT NULL DEFAULT '0',
  `asignar_aplicadores` tinyint(1) NOT NULL DEFAULT '0',
  `grupos_globales` tinyint(1) NOT NULL DEFAULT '0',
  `crear_materias` tinyint(1) NOT NULL DEFAULT '0',
  `admin_carreras` tinyint(1) NOT NULL DEFAULT '0',
  `admin_evaluaciones` tinyint(1) NOT NULL DEFAULT '0',
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
-- Volcar la base de datos para la tabla `Materias`
--

INSERT INTO `Materias` (`Clave`, `Descripcion`) VALUES
('CC313', 'Administración de Bases de Datos'),
('CC316', 'Análisis y Diseño de Algoritmos'),
('CC210', 'Arquitectura de Computadoras'),
('CC409', 'Arquitectura de Computadoras Avanzada'),
('CC403', 'Auditoria de Sistemas'),
('CC302', 'Bases de Datos'),
('CC309', 'Bases de Datos Avanzadas'),
('CC317', 'Compiladores'),
('CC411', 'Computación Tolerante a Fallas'),
('CC204', 'Estructura de Archivos'),
('CC202', 'Estructura de Datos'),
('CC321', 'Fundamentos de Ingeniería de Software'),
('CC311', 'Gráficas por Computadora'),
('CC304', 'Ingeniería de Software I'),
('CC305', 'Ingeniería de Software II'),
('CC415', 'Inteligencia Artificial'),
('CC100', 'Introducción a la Computación'),
('CC102', 'Introducción a la Programación'),
('CC208', 'Lenguajes de Programación Comparados'),
('CC322', 'Organización de Computadoras I'),
('CC323', 'Organización de Computadoras II'),
('CC413', 'Programación Concurrente y Distribuida'),
('CC206', 'Programación de Sistemas'),
('CC401', 'Programación de Sistemas Multimedia'),
('CC108', 'Programacion Estructurada'),
('CC307', 'Programación Lógica y Funcional'),
('CC200', 'Programación Orientada a Objetos'),
('CC109', 'Programación para Interfaces'),
('CC407', 'Proyecto Terminal'),
('CC212', 'Redes de Computadoras'),
('CC324', 'Redes de Computadoras Avanzadas'),
('CC410', 'Redes Neuronales Artificiales'),
('CC408', 'Simulación de Sistemas Digitales'),
('CC315', 'Sistemas de Información Administrativos'),
('CC404', 'Sistemas de Información Financieros'),
('CC405', 'Sistemas de Información para la Manufactura'),
('CC400', 'Sistemas Expertos'),
('CC300', 'Sistemas Operativos'),
('CC319', 'Sistemas Operativos Avanzados'),
('CC314', 'Taller de Administración de Bases de Datos'),
('CC303', 'Taller de Bases de Datos'),
('CC310', 'Taller de Bases de Datos Avanzadas'),
('CC318', 'Taller de Compiladores'),
('CC205', 'Taller de Estructura de Archivos'),
('CC203', 'Taller de Estructura de Datos'),
('CC312', 'Taller de Gráficas por Computadora'),
('CC306', 'Taller de Ingeniería de Software II'),
('CC101', 'Taller de Introducción a la Computación'),
('CC414', 'Taller de Programación Concurrente y Distribuida'),
('CC207', 'Taller de Programación de Sistemas'),
('CC103', 'Taller de Programación Estructurada'),
('CC308', 'Taller de Programación Lógica y Funcional'),
('CC201', 'Taller de Programación Orientada a Objetos'),
('CC325', 'Taller de Redes Avanzadas'),
('CC213', 'Taller de Redes de Computadoras'),
('CC301', 'Taller de Sistemas Operativos'),
('CC320', 'Taller de Sistemas Operativos Avanzados'),
('CC211', 'Teleinformática'),
('CC209', 'Teoría de la Computación'),
('CC417', 'Topicos Selectos de Computación I'),
('CC418', 'Topicos Selectos de Computación II'),
('CC419', 'Topicos Selectos de Computación III'),
('CC420', 'Topicos Selectos de Informática I'),
('CC421', 'Topicos Selectos de Informática II'),
('CC422', 'Topicos Selectos de Informática III');

--
-- Volcar la base de datos para la tabla `Evaluaciones`
--

INSERT INTO `Evaluaciones` (`Id`, `Descripcion`) VALUES
(0, 'Extraordinario');


