DROP DATABASE IF EXISTS sistema_gimnasios;
CREATE DATABASE sistema_gimnasios;
USE sistema_gimnasios;

-- =====================================
-- TABLA GYMS
-- =====================================
CREATE TABLE gyms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(150),
    telefono VARCHAR(30),
    email VARCHAR(100),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================
-- TABLA USUARIOS
-- =====================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NULL,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin_general','admin_gym','empleado') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
);

-- =====================================
-- TABLA CLIENTES
-- =====================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    dni VARCHAR(20),
    telefono VARCHAR(30),
    email VARCHAR(100),
    fecha_nacimiento DATE,
    fecha_alta DATE NOT NULL,
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    
    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE
);

-- =====================================
-- TABLA PLANES (CATÁLOGO GLOBAL)
-- =====================================
CREATE TABLE planes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio_base DECIMAL(10,2) DEFAULT 0,
    duracion_dias INT DEFAULT 30
);

-- Planes base que querías
INSERT INTO planes (nombre, precio_base, duracion_dias) VALUES
('Pase libre', 0, 30),
('Aparatos', 0, 30),
('Funcional', 0, 30),
('Zumba', 0, 30),
('Día', 0, 1);

-- =====================================
-- TABLA PLANES POR GYM
-- =====================================
CREATE TABLE gym_planes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    plan_id INT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    activo TINYINT(1) DEFAULT 1,

    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES planes(id) ON DELETE CASCADE
);

-- =====================================
-- TABLA PAGOS
-- =====================================
CREATE TABLE pagos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    gym_id INT NOT NULL,
    cliente_id INT NOT NULL,
    gym_plan_id INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    fecha_pago DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,

    FOREIGN KEY (gym_id) REFERENCES gyms(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (gym_plan_id) REFERENCES gym_planes(id) ON DELETE CASCADE
);