-- Add role column to users table
ALTER TABLE users
ADD COLUMN role ENUM('client', 'trainer', 'admin') NOT NULL DEFAULT 'client' AFTER status;

-- Create trainers_clients relationship table
CREATE TABLE IF NOT EXISTS trainers_clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    trainer_id INT NOT NULL,
    client_id INT NOT NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_trainer_client (trainer_id, client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create client_plans table to track assigned training plans
CREATE TABLE IF NOT EXISTS client_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    trainer_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES training_plans(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add trainer_id to training_plans table
ALTER TABLE training_plans
ADD COLUMN trainer_id INT AFTER id,
ADD FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE;
