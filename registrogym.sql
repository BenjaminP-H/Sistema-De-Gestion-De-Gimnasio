CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(50) NOT NULL,
    apellidos VARCHAR(50) NOT NULL,
    dni VARCHAR(20) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    foto_carnet VARCHAR(255) DEFAULT NULL,
    fecha_registro DATE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* ===========================
   TABLA PLANES
=========================== */
CREATE TABLE planes (
    id_plan INT AUTO_INCREMENT PRIMARY KEY,
    nombre_plan VARCHAR(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/* ===========================
   DATOS INICIALES - PLANES
=========================== */
INSERT INTO planes (nombre_plan) VALUES
('Pase libre'),
('Aparatos'),
('Funcional'),
('Zumba'),
('Día');


/* ===========================
   TABLA PAGOS
=========================== */
CREATE TABLE pagos (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_plan INT NOT NULL,
    fecha_pago DATE NOT NULL,
    dias_pagados INT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    modo_pago ENUM('Efectivo','Transferencia') NOT NULL,
    estado ENUM('Pagado','Vencido','Cancelado') NOT NULL,

    CONSTRAINT fk_pago_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES clientes(id_cliente)
        ON DELETE CASCADE,

    CONSTRAINT fk_pago_plan
        FOREIGN KEY (id_plan)
        REFERENCES planes(id_plan)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


/* ===========================
   TABLA USUARIOS
=========================== */
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin','empleado') DEFAULT 'empleado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
