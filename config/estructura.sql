SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE `reservas`;
DROP TABLE `fotografias`;
DROP TABLE `usuarios`;
DROP TABLE `habitaciones`;
DROP TABLE `log`;

CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb3_spanish_ci NOT NULL,
  `apellidos` varchar(100) COLLATE utf8mb3_spanish_ci NOT NULL,
  `dni` char(9) COLLATE utf8mb3_spanish_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb3_spanish_ci NOT NULL,
  `clave` varchar(255) COLLATE utf8mb3_spanish_ci NOT NULL,
  `numero_tarjeta` char(16) COLLATE utf8mb3_spanish_ci NOT NULL,
  `rol` enum('Cliente','Recepcionista','Administrador') COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

CREATE TABLE `habitaciones` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_habitacion` varchar(50) COLLATE utf8mb3_spanish_ci NOT NULL,
  `capacidad` int NOT NULL,
  `precio_noche` decimal(10,2) NOT NULL,
  `descripcion` text COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_habitacion` (`numero_habitacion`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

CREATE TABLE `fotografias` (
  `id_img` int NOT NULL AUTO_INCREMENT,
  `id_habitacion` int NOT NULL,
  `url` varchar(255) COLLATE utf8mb3_spanish_ci NOT NULL,
  PRIMARY KEY (`id_img`),
  KEY `id_habitacion` (`id_habitacion`),
  CONSTRAINT `fotografias_ibfk_1` FOREIGN KEY (`id_habitacion`) REFERENCES `habitaciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

CREATE TABLE `log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fecha_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  `descripcion` varchar(255) COLLATE utf8mb3_spanish_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

CREATE TABLE `reservas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_habitacion` int NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `comentarios` text COLLATE utf8mb3_spanish_ci,
  `n_personas` int DEFAULT NULL,
  `estado` enum('Pendiente','Confirmada') COLLATE utf8mb3_spanish_ci DEFAULT 'Pendiente',
  `marca_tiempo` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_usuario` (`id_usuario`),
  KEY `id_habitacion` (`id_habitacion`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`id_habitacion`) REFERENCES `habitaciones` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_spanish_ci;

INSERT INTO usuarios VALUES('1', 'Tia', 'Rodriguez', '66370306B', 'tia@void.ugr.es', '$2y$10$A67xFtax9R21QRCK2uwXfu5kWxBKs3fZjQmgsWShrpchviw2WcyUy', '4749746000945127', 'Administrador');

SET FOREIGN_KEY_CHECKS = 1;