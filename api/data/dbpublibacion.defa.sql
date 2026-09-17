-- 1. Insertar datos en la tabla 'usuario'
-- Las contraseñas están representadas con hashes ficticios
INSERT INTO usuario (username, password, rol, activo) VALUES
('admin_jose', '$2y$10$e0MYzXyDx', 'Administrador', 1),
('maria_personal', '$2y$10$92IXUNpkj', 'Personal', 1),
('carlos_lector', '$2y$10$86pXUvSkq', 'Lector', 1);

-- 2. Insertar datos en la tabla 'lector'
-- Vinculados opcionalmente a los usuarios creados
INSERT INTO lector (CI, nombre, apellidos, email, cod_usuario) VALUES
('1234567-LP', 'Carlos', 'Mendoza Ruiz', 'carlos.mendoza@email.com', 3),
('7654321-SC', 'Ana', 'Gomez Vaca', 'ana.gomez@email.com', NULL),
('4567890-CB', 'Luis', 'Fernandez Arce', 'luis.fer@email.com', NULL);

-- 3. Insertar datos en la tabla 'autor'
INSERT INTO autor (CI, nombre, apellidos, biografia) VALUES
('1111111-LP', 'Gabriel', 'García Márquez', 'Escritor colombiano, premio Nobel de Literatura en 1982.'),
('2222222-SC', 'Isabel', 'Allende', 'Escritora chilena, una de las autoras más leídas del mundo.'),
('3333333-CB', 'Franz', 'Kafka', 'Escritor bohemio en idioma alemán, cuya obra influyó en el existencialismo.');

-- 4. Insertar datos en la tabla 'libro'
INSERT INTO libro (isbn, titulo, sinopsis, estado_publicacion, fecha_publicacion) VALUES
('978-0307474728', 'Cien años de soledad', 'La historia de la familia Buendía en el pueblo ficticio de Macondo.', 'Publicado', '1967-05-30'),
('978-1501117015', 'La casa de los espíritus', 'Narra la historia de cuatro generaciones de la familia Trueba.', 'Publicado', '1982-10-01'),
('978-8420651361', 'La metamorfosis', 'La transformación de Gregorio Samsa en un monstruoso insecto.', 'Publicado', '1915-10-15');

-- 5. Insertar datos en la tabla relacional 'autor_libro'
INSERT INTO autor_libro (cod_libro, cod_autor, tipo_participacion) VALUES
(1, 1, 'Autor Principal'),
(2, 2, 'Autor Principal'),
(3, 3, 'Autor Principal');

-- 6. Insertar datos en la tabla 'lectura_libro'
-- Utiliza CURRENT_DATE por defecto si no se especifica la fecha
INSERT INTO lectura_libro (cod_lector, cod_libro, fecha_inicio) VALUES
(1, 1, '2026-01-15'),
(2, 2, '2026-02-20'),
(3, 3, '2026-03-01');