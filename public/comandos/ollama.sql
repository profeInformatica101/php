-- Crea BD y usa collation compatible
CREATE DATABASE IF NOT EXISTS chatdb
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE chatdb;

CREATE TABLE threads (
  id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  uuid          CHAR(36) NOT NULL UNIQUE,
  title         VARCHAR(200) NULL,
  model         VARCHAR(100) NOT NULL,
  system_prompt TEXT NULL,
  keep_alive    VARCHAR(20) DEFAULT '10m',
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
  id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  thread_id     BIGINT UNSIGNED NOT NULL,
  role          ENUM('system','user','assistant') NOT NULL,
  content       MEDIUMTEXT NOT NULL,
  token_count   INT NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_thread_created (thread_id, created_at),
  CONSTRAINT fk_messages_thread
    FOREIGN KEY (thread_id) REFERENCES threads(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- OPCIONAL (solo si tu servidor lo soporta):
-- ALTER TABLE messages ADD FULLTEXT INDEX ft_content (content);
