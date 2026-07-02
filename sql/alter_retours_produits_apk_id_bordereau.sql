-- Bordereau APK : lier les lignes d'un meme envoi (+ photo)
-- (applique aussi automatiquement au premier appel API tracky.php si la colonne manque)
ALTER TABLE retours_produits_apk
  ADD COLUMN id_bordereau VARCHAR(50) NULL DEFAULT NULL AFTER user_id;
