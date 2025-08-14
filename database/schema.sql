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

-- Base de datos para el control de medicamentos
CREATE DATABASE IF NOT EXISTS medication_control;
USE medication_control;

-- Tabla de usuarios
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'pharmacist', 'assistant') NOT NULL DEFAULT 'assistant',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de categorías de medicamentos
CREATE TABLE medication_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de medicamentos
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    generic_name VARCHAR(200),
    category_id INT,
    active_ingredient VARCHAR(200),
    dosage_form VARCHAR(100),
    strength VARCHAR(100),
    manufacturer VARCHAR(200),
    description TEXT,
    side_effects TEXT,
    contraindications TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    requires_prescription BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES medication_categories(id)
);

-- Tabla de inventario
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medication_id INT NOT NULL,
    batch_number VARCHAR(100),
    expiration_date DATE NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL,
    supplier VARCHAR(200),
    location VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (medication_id) REFERENCES medications(id)
);

-- Tabla de transacciones de inventario
CREATE TABLE inventory_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inventory_id INT NOT NULL,
    user_id INT NOT NULL,
    transaction_type ENUM('in', 'out', 'adjustment', 'expired') NOT NULL,
    quantity INT NOT NULL,
    reason TEXT,
    transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_id) REFERENCES inventory(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabla de prescripciones
CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_name VARCHAR(200) NOT NULL,
    patient_id VARCHAR(100),
    doctor_name VARCHAR(200),
    doctor_license VARCHAR(100),
    prescription_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de medicamentos en prescripciones
CREATE TABLE prescription_medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    medication_id INT NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    frequency VARCHAR(100) NOT NULL,
    duration VARCHAR(100),
    quantity_prescribed INT NOT NULL,
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
    FOREIGN KEY (medication_id) REFERENCES medications(id)
);

-- Tabla de ventas
CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'card', 'insurance') NOT NULL,
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Tabla de medicamentos vendidos
CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    medication_id INT NOT NULL,
    inventory_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id),
    FOREIGN KEY (medication_id) REFERENCES medications(id),
    FOREIGN KEY (inventory_id) REFERENCES inventory(id)
);

-- Insertar datos de ejemplo
INSERT INTO users (username, email, password_hash, full_name, role) VALUES
('admin', 'admin@farmacia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin'),
('farmacia1', 'farmacia1@farmacia.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Farmacéutico Principal', 'pharmacist');

INSERT INTO medication_categories (name, description) VALUES
('Analgésicos', 'Medicamentos para el alivio del dolor'),
('Antibióticos', 'Medicamentos para combatir infecciones bacterianas'),
('Antiinflamatorios', 'Medicamentos para reducir la inflamación'),
('Antihistamínicos', 'Medicamentos para alergias'),
('Vitaminas', 'Suplementos vitamínicos');

INSERT INTO medications (name, generic_name, category_id, active_ingredient, dosage_form, strength, manufacturer, description) VALUES
('Paracetamol', 'Acetaminofén', 1, 'Paracetamol', 'Tableta', '500mg', 'Genérico', 'Analgésico y antipirético'),
('Ibuprofeno', 'Ibuprofeno', 3, 'Ibuprofeno', 'Tableta', '400mg', 'Genérico', 'Antiinflamatorio no esteroideo'),
('Amoxicilina', 'Amoxicilina', 2, 'Amoxicilina', 'Cápsula', '500mg', 'Genérico', 'Antibiótico de amplio espectro');
