-- Nutrition tracking tables
CREATE TABLE IF NOT EXISTS nutrition_goals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    calories_target INT NOT NULL,
    protein_target INT NOT NULL,
    water_target DECIMAL(3,1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS nutrition_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    calories_consumed INT NOT NULL,
    protein_consumed INT NOT NULL,
    water_consumed DECIMAL(3,1) NOT NULL,
    log_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Progress tracking tables
CREATE TABLE IF NOT EXISTS progress_measurements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    weight DECIMAL(4,1),
    body_fat DECIMAL(4,1),
    muscle_mass DECIMAL(4,1),
    measurement_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Insert default nutrition goals for testing
INSERT INTO nutrition_goals (user_id, calories_target, protein_target, water_target)
VALUES (1, 2400, 150, 3.0);

-- Insert sample progress data for testing
INSERT INTO progress_measurements (user_id, weight, body_fat, muscle_mass, measurement_date)
VALUES 
(1, 80.0, 22.0, 58.0, '2023-01-01'),
(1, 79.5, 21.5, 58.2, '2023-02-01'),
(1, 78.8, 21.0, 58.5, '2023-03-01');
