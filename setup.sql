-- Script de configuración inicial para la API de Medicamentos
-- Ejecutar como root o usuario con permisos administrativos

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS medicamentos_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Usar la base de datos
USE medicamentos_db;

-- Crear tabla de medicamentos
CREATE TABLE IF NOT EXISTS medicamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL UNIQUE,
    descripcion TEXT,
    presentacion VARCHAR(255) NOT NULL,
    dosis_recomendada VARCHAR(255) NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_nombre (nombre),
    INDEX idx_stock (stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos de ejemplo
INSERT INTO medicamentos (nombre, descripcion, presentacion, dosis_recomendada, stock) VALUES
('Paracetamol', 'Analgésico y antipirético para el alivio del dolor y la fiebre', 'Tabletas 500mg', '1 tableta cada 8 horas', 150),
('Ibuprofeno', 'Antiinflamatorio no esteroideo (AINE)', 'Tabletas 400mg', '1 tableta cada 12 horas', 75),
('Amoxicilina', 'Antibiótico de amplio espectro', 'Cápsulas 500mg', '1 cápsula cada 8 horas', 200),
('Omeprazol', 'Inhibidor de la bomba de protones', 'Cápsulas 20mg', '1 cápsula en ayunas', 80),
('Losartán', 'Bloqueador de receptores de angiotensina', 'Tabletas 50mg', '1 tableta diaria', 120),
('Metformina', 'Antidiabético oral', 'Tabletas 850mg', '1 tableta con las comidas', 90),
('Simvastatina', 'Reductor del colesterol', 'Tabletas 20mg', '1 tableta por la noche', 60),
('Salbutamol', 'Broncodilatador', 'Inhalador 100mcg', '2 puffs según necesidad', 45);

-- Crear usuario específico para la aplicación (opcional)
-- CREATE USER 'medicamentos_user'@'localhost' IDENTIFIED BY 'password_seguro';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON medicamentos_db.* TO 'medicamentos_user'@'localhost';
-- FLUSH PRIVILEGES;

-- Mostrar tabla creada
DESCRIBE medicamentos;

-- Verificar datos insertados
SELECT COUNT(*) as total_medicamentos FROM medicamentos;