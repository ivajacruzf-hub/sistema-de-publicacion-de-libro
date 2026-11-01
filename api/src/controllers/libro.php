<?php
require_once __DIR__ . '/ResourceController.php';
require_once __DIR__ . '/../models/AuthorBooks.php';
require_once __DIR__ . '/../security/ApiAuth.php';

class libroController extends ResourceController
{
    protected string $table = 'libro';
    protected array $fields = ['isbn', 'titulo', 'sinopsis', 'portada', 'genero', 'estado_publicacion', 'fecha_publicacion'];
    protected array $requiredFields = ['titulo'];

    public function getAll(): void
    {
        try {
            $this->respond(ConexionPDO::query(
                'SELECT id, isbn, titulo, sinopsis, genero, estado_publicacion, fecha_publicacion
                 FROM libro ORDER BY id'
            ));
        } catch (Throwable $error) {
            error_log('Error al consultar libros: ' . $error);
            http_response_code(500);
            $this->respond([
                'error' => 'No se pudieron consultar los libros. ' . ConexionPDO::connectionHint($error)
            ]);
        }
    }

    public function getMine(): void
    {
        $user = ApiAuth::requireAuthor();
        if ($user === null) {
            return;
        }

        try {
            $this->respond(AuthorBooks::allForOwner((int) $user['id']));
        } catch (Throwable $error) {
            error_log('Error al consultar libros propios del autor: ' . $error);
            http_response_code(500);
            $this->respond(['error' => 'No se pudieron consultar tus libros']);
        }
    }

    public function addMine(): void
    {
        $user = ApiAuth::requireAuthor();
        if ($user === null) {
            return;
        }

        $data = $this->readJson();
        if ($data === null || !$this->validate($data, true)) {
            return;
        }

        try {
            $id = AuthorBooks::createForOwner((int) $user['id'], $data);
            http_response_code(201);
            $this->respond(['id' => $id, 'message' => 'Libro creado correctamente']);
        } catch (Throwable $error) {
            error_log('Error al crear libro del autor: ' . $error);
            http_response_code(409);
            $this->respond(['error' => 'No se pudo crear el libro; revisa que los valores sean válidos']);
        }
    }

    public function updateMine(int $id): void
    {
        $user = ApiAuth::requireAuthor();
        if ($user === null) {
            return;
        }

        $data = $this->readJson();
        if ($data === null || !$this->validate($data, false)) {
            return;
        }

        try {
            if (!AuthorBooks::updateForOwner($id, (int) $user['id'], $data)) {
                http_response_code(404);
                $this->respond(['error' => 'Libro no encontrado']);
                return;
            }
            $this->respond(['message' => 'Libro actualizado correctamente']);
        } catch (Throwable $error) {
            error_log('Error al actualizar libro del autor: ' . $error);
            http_response_code(409);
            $this->respond(['error' => 'No se pudo actualizar el libro; revisa que los valores sean válidos']);
        }
    }

    public function deleteMine(int $id): void
    {
        $user = ApiAuth::requireAuthor();
        if ($user === null) {
            return;
        }

        try {
            if (!AuthorBooks::deleteForOwner($id, (int) $user['id'])) {
                http_response_code(404);
                $this->respond(['error' => 'Libro no encontrado']);
                return;
            }
            $this->respond(['message' => 'Libro eliminado correctamente']);
        } catch (Throwable $error) {
            error_log('Error al eliminar libro del autor: ' . $error);
            http_response_code(409);
            $this->respond(['error' => 'No se pudo eliminar el libro']);
        }
    }

    public function add(): void
    {
        $this->rejectUnscopedWrite();
    }

    public function update(...$ids): void
    {
        $this->rejectUnscopedWrite();
    }

    public function delete(...$ids): void
    {
        $this->rejectUnscopedWrite();
    }

    private function rejectUnscopedWrite(): void
    {
        http_response_code(403);
        $this->respond(['error' => 'Usa las rutas /mis-libros para administrar libros propios']);
    }

    protected function validateValues(array $data): ?string
    {
        $states = ['En Redacción', 'En Revisión', 'Publicado', 'Archivado'];
        if (isset($data['estado_publicacion']) && !in_array($data['estado_publicacion'], $states, true)) {
            return 'El estado de publicación no es válido';
        }
        if (isset($data['genero']) && strlen((string) $data['genero']) > 100) {
            return 'El género no puede superar los 100 caracteres';
        }
        if (array_key_exists('portada', $data) && $data['portada'] !== null && $data['portada'] !== '') {
            if (!is_string($data['portada'])) {
                return 'La portada debe ser una URL HTTP o HTTPS válida';
            }
            $coverUrl = $data['portada'];
            if (
                strlen($coverUrl) > 2048
                || filter_var($coverUrl, FILTER_VALIDATE_URL) === false
                || !in_array(strtolower((string) parse_url($coverUrl, PHP_URL_SCHEME)), ['http', 'https'], true)
            ) {
                return 'La portada debe ser una URL HTTP o HTTPS válida de hasta 2048 caracteres';
            }
        }
        if (isset($data['fecha_publicacion']) && $data['fecha_publicacion'] !== '') {
            $date = (string) $data['fecha_publicacion'];
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
                || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))) {
                return 'La fecha de publicación debe tener el formato AAAA-MM-DD';
            }
        }
        return null;
    }
}