-- Ejecutar una sola vez en la base existente seleccionada para la API.
-- No elimina ni modifica los datos existentes de libro.
ALTER TABLE libro ADD COLUMN portada VARCHAR(2048) NULL;
