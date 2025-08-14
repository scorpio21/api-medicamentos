-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS medication_control CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE medication_control;

-- Tabla de medicamentos
CREATE TABLE IF NOT EXISTS medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL COMMENT 'Nombre del medicamento',
    description TEXT COMMENT 'Descripción del medicamento',
    active_ingredient VARCHAR(255) NOT NULL COMMENT 'Ingrediente activo',
    dosage_form VARCHAR(100) COMMENT 'Forma de dosificación (tableta, cápsula, jarabe, etc.)',
    strength VARCHAR(100) COMMENT 'Concentración del medicamento',
    manufacturer VARCHAR(255) COMMENT 'Fabricante del medicamento',
    batch_number VARCHAR(100) NOT NULL UNIQUE COMMENT 'Número de lote',
    expiry_date DATE NOT NULL COMMENT 'Fecha de vencimiento',
    quantity INT NOT NULL DEFAULT 0 COMMENT 'Cantidad en stock',
    storage_conditions TEXT COMMENT 'Condiciones de almacenamiento',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Fecha de creación',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Fecha de última actualización',
    
    INDEX idx_name (name),
    INDEX idx_active_ingredient (active_ingredient),
    INDEX idx_manufacturer (manufacturer),
    INDEX idx_batch_number (batch_number),
    INDEX idx_expiry_date (expiry_date),
    INDEX idx_quantity (quantity),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla de medicamentos';

-- Insertar datos de ejemplo
INSERT INTO medications (
    name, description, active_ingredient, dosage_form, strength, 
    manufacturer, batch_number, expiry_date, quantity, storage_conditions
) VALUES 
('Paracetamol 500mg', 'Analgésico y antipirético para el alivio del dolor y la fiebre', 'Paracetamol', 'Tableta', '500mg', 'Genérico', 'BATCH001', '2025-12-31', 150, 'Almacenar en lugar seco y fresco, protegido de la luz'),
('Ibuprofeno 400mg', 'Antiinflamatorio no esteroideo para el alivio del dolor y la inflamación', 'Ibuprofeno', 'Tableta', '400mg', 'Genérico', 'BATCH002', '2025-10-15', 200, 'Almacenar en lugar seco y fresco'),
('Amoxicilina 500mg', 'Antibiótico de amplio espectro para el tratamiento de infecciones bacterianas', 'Amoxicilina', 'Cápsula', '500mg', 'Genérico', 'BATCH003', '2024-06-30', 75, 'Almacenar en lugar seco y fresco, protegido de la luz'),
('Omeprazol 20mg', 'Protector gástrico para el tratamiento de úlceras y reflujo', 'Omeprazol', 'Cápsula', '20mg', 'Genérico', 'BATCH004', '2025-08-20', 120, 'Almacenar en lugar seco y fresco'),
('Loratadina 10mg', 'Antihistamínico para el alivio de síntomas alérgicos', 'Loratadina', 'Tableta', '10mg', 'Genérico', 'BATCH005', '2025-11-30', 180, 'Almacenar en lugar seco y fresco'),
('Metformina 500mg', 'Antidiabético oral para el control de la diabetes tipo 2', 'Metformina', 'Tableta', '500mg', 'Genérico', 'BATCH006', '2025-09-15', 90, 'Almacenar en lugar seco y fresco'),
('Amlodipino 5mg', 'Bloqueador de canales de calcio para el tratamiento de la hipertensión', 'Amlodipino', 'Tableta', '5mg', 'Genérico', 'BATCH007', '2025-07-30', 110, 'Almacenar en lugar seco y fresco'),
('Simvastatina 20mg', 'Estatinas para el control del colesterol', 'Simvastatina', 'Tableta', '20mg', 'Genérico', 'BATCH008', '2025-12-15', 85, 'Almacenar en lugar seco y fresco'),
('Losartán 50mg', 'Bloqueador del receptor de angiotensina para el tratamiento de la hipertensión', 'Losartán', 'Tableta', '50mg', 'Genérico', 'BATCH009', '2025-10-30', 95, 'Almacenar en lugar seco y fresco'),
('Atorvastatina 10mg', 'Estatinas para el control del colesterol', 'Atorvastatina', 'Tableta', '10mg', 'Genérico', 'BATCH010', '2025-11-15', 70, 'Almacenar en lugar seco y fresco');

-- Crear vista para medicamentos próximos a vencer
CREATE OR REPLACE VIEW medications_expiring_soon AS
SELECT 
    id, name, active_ingredient, manufacturer, batch_number, 
    expiry_date, quantity, storage_conditions,
    DATEDIFF(expiry_date, CURDATE()) as days_until_expiry
FROM medications 
WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
ORDER BY expiry_date ASC;

-- Crear vista para medicamentos con stock bajo
CREATE OR REPLACE VIEW medications_low_stock AS
SELECT 
    id, name, active_ingredient, manufacturer, batch_number, 
    expiry_date, quantity, storage_conditions
FROM medications 
WHERE quantity <= 20
ORDER BY quantity ASC;