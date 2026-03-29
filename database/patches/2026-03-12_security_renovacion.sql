-- Add fecha_vencimiento to pagos and backfill existing rows
ALTER TABLE pagos
    ADD COLUMN fecha_vencimiento DATE NULL;

UPDATE pagos
SET fecha_vencimiento = DATE_ADD(fecha_pago, INTERVAL dias_pagados DAY)
WHERE fecha_vencimiento IS NULL;

ALTER TABLE pagos
    MODIFY fecha_vencimiento DATE NOT NULL;

-- Login rate limiting storage
CREATE TABLE IF NOT EXISTS login_intentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    llave VARCHAR(120) NOT NULL UNIQUE,
    intentos INT NOT NULL DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

