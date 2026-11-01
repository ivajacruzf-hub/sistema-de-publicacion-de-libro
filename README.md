# SISTEMASDEPUBLICACIONDELIBROS
## SISTEMASDEPUBLICACIONDELIBROS en git

## Gestión de autores

Administra los autores desde el módulo **Autores** de la interfaz. La API permite crear, consultar, actualizar y eliminar sus registros.

## Libros, autores y lectores

La API PHP expone operaciones CRUD para estos recursos:

| Recurso | Endpoint |
| --- | --- |
| Libros | `/libros` |
| Autores | `/autores` |
| Lectores | `/lectores` |

Cada endpoint admite `GET` para listar, `POST` para crear, `PUT /{id}` para actualizar y `DELETE /{id}` para eliminar. También se conservan `/libro`, `/autor` y `/lector` como alias de compatibilidad.

En la gestión de usuarios solo se permiten los roles `Lector` y `Autor`. Al conectarse, la API convierte los roles anteriores distintos de `Lector` a `Autor`.

Configura `api/.env` con `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_PORT` y `DB_CHARSET`. Añade `API_TOKEN_SECRET` con al menos 32 caracteres aleatorios (por ejemplo, genera 48 bytes aleatorios con un generador criptográfico); no uses una clave de ejemplo ni la publiques. El login emite tokens firmados que caducan a las 12 horas, y la API valida la cuenta activa y el rol en cada operación protegida. Ejecuta `api/data/dbpublicaciones.sql` sobre la base de datos indicada en `DB_NAME`; incluye las tablas de publicaciones. La columna `id` es generada por la base de datos. La API adapta automáticamente la tabla `usuarios` antigua (con `email`, `password_hash` o campos de nombre obligatorios) al esquema que usa la gestión de usuarios, conservando sus registros y haciendo opcionales los campos heredados que el formulario actual no envía.

Para crear registros de prueba (un usuario, un autor, un lector y un libro vinculado al autor), ejecuta desde la raíz del proyecto `php api\data\seed_demo.php`. El script no duplica esos registros si ya existen y muestra una contraseña aleatoria solo al crear `usuario_demo`; guárdala, porque no se puede recuperar. Necesitas tener configurada la conexión en `api/.env` y haber creado las tablas con `api\data\dbpublicaciones.sql`.

La interfaz usa la URL de API configurada en la etiqueta `api-base-url` de `frontend/index.html`. Si el servidor de API no está en `http://api.sistemasdepublicaciondelibros`, reemplaza ese valor por la URL base del servidor (por ejemplo, `http://localhost:8000`). Apache debe permitir el uso de `.htaccess` en `api/public` para que las rutas de la API se reescriban a `index.php`; la imagen Docker del proyecto ya habilita esta configuración. La API debe permitir CORS si la interfaz se publica en otro origen.

Los libros creados desde **Autores > Mis libros** se guardan en la tabla existente `libro` con `cod_usuario` vinculado al usuario autenticado y `genero` opcional. Al conectarse, la API agrega esas columnas y su índice/relación si faltan; los libros existentes conservan `cod_usuario = NULL` y no se asignan a ninguna cuenta automáticamente. Los endpoints `GET/POST /mis-libros` y `PUT/DELETE /mis-libros/{id}` exigen un token de autor y restringen cada consulta por el ID incluido en el token. Las escrituras genéricas en `/libros` y `/libro` se rechazan; el catálogo de lectura permanece disponible.
