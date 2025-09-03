CREATE TABLE `registrogym`.`clientes` ( --tabla de clientes
    `id_cliente` INT NOT NULL AUTO_INCREMENT,
    `nombres` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `apellidos` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    `dni` VARCHAR(20) NOT NULL,
    `telefono` VARCHAR(20) NOT NULL,
    `gmail` VARCHAR(100) NULL DEFAULT NULL,
    `foto_carnet` VARCHAR(255) NULL DEFAULT NULL,
    `fecha_registro` DATE NOT NULL,
    PRIMARY KEY (`id_cliente`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `registrogym`.`planes` ( --tabla de planes
    `id_plan` INT NOT NULL AUTO_INCREMENT,
    `nombre_plan` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
    PRIMARY KEY (`id_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `registrogym`.`pagos` ( --tabla de pagos
    `id_pago` INT NOT NULL AUTO_INCREMENT,
    `id_cliente` INT NOT NULL,
    `id_plan` INT NOT NULL,
    `fecha_pago` DATE NOT NULL,
    `dias_pagados` INT NOT NULL,
    `monto` DECIMAL(10,2) NOT NULL,
    `modo_pago` ENUM('Efectivo','Transferencia') NOT NULL,
    `estado` ENUM('Pagado','Vencido','Cancelado') NOT NULL,
    `fecha_vencimiento` DATE NOT NULL,
    `comentarios` TEXT NULL,
    PRIMARY KEY (`id_pago`),
    FOREIGN KEY (`id_cliente`) REFERENCES `clientes`(`id_cliente`) ON DELETE CASCADE,
    FOREIGN KEY (`id_plan`) REFERENCES `planes`(`id_plan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `registrogym`.`usuarios` ( --tabla de usuarios
    `id_usuario` INT NOT NULL AUTO_INCREMENT,
    `usuario` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `rol` ENUM('admin','empleado') DEFAULT 'empleado',
    PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
