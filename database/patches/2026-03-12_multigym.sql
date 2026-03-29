-- Migración a esquema multi-gym (una sola DB)
-- Revisar nombres de índices antes de ejecutar DROP INDEX si tu versión difiere.

CREATE TABLE IF NOT EXISTS gyms (
    id_gym INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(150),
    telefono VARCHAR(30),
    email VARCHAR(100),
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO gyms (nombre)
SELECT 'Gym principal'
WHERE NOT EXISTS (SELECT 1 FROM gyms);

-- Agregar id_gym a tablas existentes (se asume gimnasio default = 1)
ALTER TABLE usuarios ADD COLUMN id_gym INT NOT NULL DEFAULT 1;
ALTER TABLE planes ADD COLUMN id_gym INT NOT NULL DEFAULT 1;
ALTER TABLE clientes ADD COLUMN id_gym INT NOT NULL DEFAULT 1;
ALTER TABLE pagos ADD COLUMN id_gym INT NOT NULL DEFAULT 1;

UPDATE usuarios SET id_gym = 1 WHERE id_gym IS NULL;
UPDATE planes SET id_gym = 1 WHERE id_gym IS NULL;
UPDATE clientes SET id_gym = 1 WHERE id_gym IS NULL;
UPDATE pagos SET id_gym = 1 WHERE id_gym IS NULL;

-- Ajustar índices únicos
-- Si tu índice de planes es UNIQUE(nombre_plan), eliminá primero ese índice:
-- DROP INDEX nombre_plan ON planes;
CREATE UNIQUE INDEX uq_plan_gym ON planes (id_gym, nombre_plan);

-- Si tu índice de clientes es UNIQUE(dni), eliminá primero ese índice:
-- DROP INDEX dni ON clientes;
CREATE UNIQUE INDEX uq_cliente_gym_dni ON clientes (id_gym, dni);

-- Claves foráneas
ALTER TABLE usuarios
    ADD CONSTRAINT fk_usuarios_gym FOREIGN KEY (id_gym) REFERENCES gyms(id_gym) ON DELETE CASCADE;

ALTER TABLE planes
    ADD CONSTRAINT fk_planes_gym FOREIGN KEY (id_gym) REFERENCES gyms(id_gym) ON DELETE CASCADE;

ALTER TABLE clientes
    ADD CONSTRAINT fk_clientes_gym FOREIGN KEY (id_gym) REFERENCES gyms(id_gym) ON DELETE CASCADE;

ALTER TABLE pagos
    ADD CONSTRAINT fk_pagos_gym FOREIGN KEY (id_gym) REFERENCES gyms(id_gym) ON DELETE CASCADE;
