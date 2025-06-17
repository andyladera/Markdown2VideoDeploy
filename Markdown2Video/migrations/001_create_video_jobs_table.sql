-- Migración para crear la tabla de trabajos de video (video_jobs)
-- Esta tabla actuará como nuestra cola de tareas.

CREATE TABLE IF NOT EXISTS `video_jobs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `status` ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending' COMMENT 'Estado del trabajo de renderizado',
  `markdown_content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
  `video_filename` VARCHAR(255) DEFAULT NULL COMMENT 'Nombre del archivo de video final',
  `error_details` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL COMMENT 'Almacena mensajes de error si el trabajo falla',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_status` (`status`),
  CONSTRAINT `fk_video_jobs_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `users`(`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
