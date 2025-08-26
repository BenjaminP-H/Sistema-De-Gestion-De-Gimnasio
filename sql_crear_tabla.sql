-- SQL para crear la tabla de usuarios del gym
CREATE DATABASE IF NOT EXISTS gym;
USE gym;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    fecha_pago DATE NOT NULL,
    tipo_membresia ENUM('diaria','semanal','mensual') NOT NULL,
    fecha_vencimiento DATE NOT NULL
);
