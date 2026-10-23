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

Configura `api/.env` con `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_PORT` y `DB_CHARSET`. Ejecuta `api/data/dbpublicaciones.sql` sobre la base de datos indicada en `DB_NAME`; incluye las tablas de publicaciones. La columna `id` es generada por la base de datos.

La interfaz usa la URL de API configurada en la etiqueta `api-base-url` de `frontend/index.html`. Si el servidor de API no está en `http://api.sistemasdepublicaciondelibros`, reemplaza ese valor por la URL que sirve `api/public/index.php` (por ejemplo, `http://localhost:8000` al ejecutar el servidor PHP en `api/public`). La API debe permitir CORS si la interfaz se publica en otro origen.