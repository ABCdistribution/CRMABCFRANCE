-- Sequence des bordereaux rappel (tracky.php : INSERT puis RAPPEL_{user_id}_{id sur 5 chiffres})
CREATE TABLE IF NOT EXISTS bordereaux_index (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
