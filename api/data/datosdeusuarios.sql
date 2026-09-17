USE DBpublicacion;

-- 1. Insertar datos en la tabla 'lector'
INSERT INTO lector (CI, nombre, apellidos, email) VALUES
('1111111A', 'Carlos', 'Mendoza Ruiz', 'carlos.mendoza@email.com'),
('2222222B', 'Ana María', 'Gómez Silva', 'ana.gomez@email.com'),
('3333333C', 'Luis Alberto', 'Torres Quispe', 'luis.torres@email.com');

-- 2. Insertar datos en la tabla 'autor'
INSERT INTO autor (CI, nombre, apellidos, biografia) VALUES
('4444444D', 'Gabriel', 'García Márquez', 'Escritor colombiano, ganador del Premio Nobel de Literatura en 1982.'),
('5555555E', 'Isabel', 'Allende Llona', 'Escritora chilena de gran éxito comercial, miembro de la Academia Estadounidense de las Artes y las Letras.'),
('6666666F', 'Mario', 'Vargas Llosa', 'Escritor y político peruano, uno de los más importantes novelistas y ensayistas contemporáneos.');

-- 3. Insertar datos en la tabla 'libro'
INSERT INTO libro(isbn, titulo, sinopsis, estado_publicacion, fecha_publicacion) VALUES
('978-0307474728', 'Cien años de soledad', 'La historia de la familia Buendía a lo largo de siete generaciones en el pueblo ficticio de Macondo.', 'Publicado', '1967-05-30'),
('978-0525433491', 'La casa de los espíritus', 'Narra la vida de la familia Trueba a lo largo de cuatro generaciones y los movimientos sociales de la época.', 'Publicado', '1982-10-01'),
('978-0307741639', 'La ciudad y los perros', 'Relato de la vida de los cadetes en el Colegio Militar Leoncio Prado, exponiendo la brutalidad del entorno.', 'Publicado', '1963-10-01');

-- 4. Insertar datos en la tabla relacional 'autor_libro'
INSERT INTO autor_libro (cod_libro, cod_autor, tipo_participacion) VALUES
(1, 1, 'Autor Principal'), -- Gabriel García Márquez escribió Cien años de soledad
(2, 2, 'Autor Principal'), -- Isabel Allende escribió La casa de los espíritus
(3, 3, 'Autor Principal'); -- Mario Vargas Llosa escribió La ciudad y los perros

-- 5. Insertar datos en la tabla 'lectura_libro'
INSERT INTO lectura_libro (cod_lector, cod_libro, fecha_inicio) VALUES
(1, 1, '2026-01-15'), -- Carlos lee Cien años de soledad
(2, 2, '2026-02-20'), -- Ana lee La casa de los espíritus
(3, 3, '2026-03-05'); -- Luis lee La ciudad y los perros

SELECT*FROM lector
SELECT*FROM autor
SELECT*FROM libro
SELECT*FROM lectura_libro
SELECT*FROM autor_libro