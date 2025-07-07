-- Cambiar la collation de la base de datos 'pagina' a utf8mb4_general_ci
ALTER DATABASE pagina CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Generar y ejecutar para cada tabla en la base de datos:
-- Reemplaza 'nombre_de_tabla' con el nombre real de cada tabla

-- Ejemplo para tabla 'usuarios':
ALTER TABLE usuarios CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Repite el comando ALTER TABLE para cada tabla en la base de datos 'pagina'
-- Puedes obtener la lista de tablas con:
-- SHOW TABLES FROM pagina;

-- Para automatizar, puedes usar este procedimiento almacenado (opcional):

DELIMITER $$

CREATE PROCEDURE ConvertAllTablesToUtf8mb4()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE tblName VARCHAR(255);
  DECLARE cur CURSOR FOR
    SELECT table_name FROM information_schema.tables WHERE table_schema = 'pagina' AND table_type = 'BASE TABLE';
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN cur;

  read_loop: LOOP
    FETCH cur INTO tblName;
    IF done THEN
      LEAVE read_loop;
    END IF;
    SET @s = CONCAT('ALTER TABLE `', tblName, '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;');
    PREPARE stmt FROM @s;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END LOOP;

  CLOSE cur;
END$$

DELIMITER ;

-- Para ejecutar el procedimiento:
-- CALL ConvertAllTablesToUtf8mb4();

-- Luego puedes eliminar el procedimiento si quieres:
-- DROP PROCEDURE ConvertAllTablesToUtf8mb4;
