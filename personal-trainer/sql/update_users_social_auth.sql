-- Add social authentication columns to users table if it doesn't exist
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    password VARCHAR(255),
    firebase_uid VARCHAR(255) UNIQUE,
    photo_url TEXT,
    auth_provider VARCHAR(50),
    last_login DATETIME,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    INDEX idx_email (email),
    INDEX idx_firebase_uid (firebase_uid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns if they don't exist
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS firebase_uid VARCHAR(255) UNIQUE AFTER password,
    ADD COLUMN IF NOT EXISTS photo_url TEXT AFTER firebase_uid,
    ADD COLUMN IF NOT EXISTS auth_provider VARCHAR(50) AFTER photo_url,
    ADD COLUMN IF NOT EXISTS last_login DATETIME AFTER auth_provider,
    ADD COLUMN IF NOT EXISTS created_at DATETIME AFTER last_login,
    ADD COLUMN IF NOT EXISTS updated_at DATETIME AFTER created_at,
    ADD COLUMN IF NOT EXISTS status ENUM('active', 'inactive', 'suspended') DEFAULT 'active' AFTER updated_at;

-- Add indexes if they don't exist
CREATE INDEX IF NOT EXISTS idx_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_firebase_uid ON users(firebase_uid);

-- Create activity log table if it doesn't exist
CREATE TABLE IF NOT EXISTS log_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(255) NOT NULL,
    detalles TEXT,
    fecha DATETIME NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_usuario_fecha (usuario_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
