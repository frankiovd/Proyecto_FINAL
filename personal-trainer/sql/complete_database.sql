CREATE DATABASE IF NOT EXISTS proyecto;
USE proyecto;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    photo_url VARCHAR(255) NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User roles table
CREATE TABLE IF NOT EXISTS user_roles (
    user_id INT NOT NULL,
    role ENUM('client', 'trainer', 'admin') DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- User trainers table
CREATE TABLE IF NOT EXISTS user_trainers (
    user_id INT NOT NULL,
    trainer_id INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, trainer_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Training plans table
CREATE TABLE IF NOT EXISTS training_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    trainer_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    difficulty_level ENUM('beginner', 'intermediate', 'advanced') NOT NULL,
    duration_weeks INT NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Plan exercises table
CREATE TABLE IF NOT EXISTS plan_exercises (
    id INT PRIMARY KEY AUTO_INCREMENT,
    plan_id INT NOT NULL,
    exercise_name VARCHAR(100) NOT NULL,
    sets INT NOT NULL,
    reps VARCHAR(20) NOT NULL,
    day VARCHAR(20) NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (plan_id) REFERENCES training_plans(id) ON DELETE CASCADE
);

-- Client plans table
CREATE TABLE IF NOT EXISTS client_plans (
    id INT PRIMARY KEY AUTO_INCREMENT,
    client_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES training_plans(id) ON DELETE CASCADE
);

-- Exercise progress table
CREATE TABLE IF NOT EXISTS exercise_progress (
    client_id INT NOT NULL,
    plan_id INT NOT NULL,
    exercise_id INT NOT NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (client_id, plan_id, exercise_id),
    FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES training_plans(id) ON DELETE CASCADE,
    FOREIGN KEY (exercise_id) REFERENCES plan_exercises(id) ON DELETE CASCADE
);

-- User settings table
CREATE TABLE IF NOT EXISTS user_settings (
    user_id INT PRIMARY KEY,
    notifications_enabled TINYINT(1) DEFAULT 1,
    email_notifications TINYINT(1) DEFAULT 1,
    language VARCHAR(5) DEFAULT 'es',
    theme VARCHAR(20) DEFAULT 'light',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Training sessions table
CREATE TABLE IF NOT EXISTS training_sessions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    trainer_id INT,
    session_date DATE NOT NULL,
    session_time TIME NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    rating INT CHECK (rating >= 1 AND rating <= 5),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE SET NULL
);

-- User achievements table
CREATE TABLE IF NOT EXISTS user_achievements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    achievement_type VARCHAR(50) NOT NULL,
    achievement_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_training_plans_trainer ON training_plans(trainer_id);
CREATE INDEX idx_plan_exercises_plan ON plan_exercises(plan_id);
CREATE INDEX idx_client_plans_client ON client_plans(client_id);
CREATE INDEX idx_client_plans_plan ON client_plans(plan_id);
CREATE INDEX idx_exercise_progress_client ON exercise_progress(client_id);
CREATE INDEX idx_training_sessions_user ON training_sessions(user_id);
CREATE INDEX idx_training_sessions_trainer ON training_sessions(trainer_id);
CREATE INDEX idx_user_achievements_user ON user_achievements(user_id);
