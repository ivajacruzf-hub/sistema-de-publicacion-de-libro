<?php
require_once __DIR__ . '/ResourceController.php';

class libroController extends ResourceController
{
    protected string $table = 'libro';
    protected array $fields = ['isbn', 'titulo', 'sinopsis', 'estado_publicacion', 'fecha_publicacion'];
    protected array $requiredFields = ['titulo'];

    protected function validateValues(array $data): ?string
    {
        $states = ['En Redacción', 'En Revisión', 'Publicado', 'Archivado'];
        if (isset($data['estado_publicacion']) && !in_array($data['estado_publicacion'], $states, true)) {
            return 'El estado de publicación no es válido';
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