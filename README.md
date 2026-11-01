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

Configura `api/.env` con `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_PORT` y `DB_CHARSET`. Ejecuta `api/data/dbpublicaciones.sql` sobre la base de datos indicada en `DB_NAME`; incluye las tablas de publicaciones. La columna `id` es generada por la base de datos. La API adapta automáticamente la tabla `usuarios` antigua (con `email`, `password_hash` o campos de nombre obligatorios) al esquema que usa la gestión de usuarios, conservando sus registros y haciendo opcionales los campos heredados que el formulario actual no envía.

Para crear registros de prueba (un usuario, un autor, un lector y un libro vinculado al autor), ejecuta desde la raíz del proyecto `php api\data\seed_demo.php`. El script no duplica esos registros si ya existen y muestra una contraseña aleatoria solo al crear `usuario_demo`; guárdala, porque no se puede recuperar. Necesitas tener configurada la conexión en `api/.env` y haber creado las tablas con `api\data\dbpublicaciones.sql`.

La interfaz usa la URL de API configurada en la etiqueta `api-base-url` de `frontend/index.html`. Si el servidor de API no está en `http://api.sistemasdepublicaciondelibros`, reemplaza ese valor por la URL base del servidor (por ejemplo, `http://localhost:8000`). Apache debe permitir el uso de `.htaccess` en `api/public` para que las rutas de la API se reescriban a `index.php`; la imagen Docker del proyecto ya habilita esta configuración. La API debe permitir CORS si la interfaz se publica en otro origen.