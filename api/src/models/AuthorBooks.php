<?php
require_once __DIR__ . '/../config/conexionDB.php';

class AuthorBooks
{
    private const FIELDS = [
        'isbn',
        'titulo',
        'sinopsis',
        'portada',
        'genero',
        'estado_publicacion',
        'fecha_publicacion'
    ];

    public static function allForOwner(int $userId): array
    {
        return ConexionPDO::query(
            'SELECT id, isbn, titulo, sinopsis, portada, genero, estado_publicacion, fecha_publicacion
             FROM libro WHERE cod_usuario = :user_id ORDER BY id DESC',
            [':user_id' => $userId]
        );
    }

    public static function createForOwner(int $userId, array $data): string
    {
        $columns = array_merge(self::FIELDS, ['cod_usuario']);
        $parameters = [];
        $values = [];
        foreach (self::FIELDS as $field) {
            $parameters[] = ':' . $field;
            $values[':' . $field] = $data[$field] ?? null;
        }
        $parameters[] = ':cod_usuario';
        $values[':cod_usuario'] = $userId;

        return ConexionPDO::execute(
            'INSERT INTO libro (`' . implode('`, `', $columns) . '`) VALUES (' . implode(', ', $parameters) . ')',
            $values,
            true
        );
    }

    public static function updateForOwner(int $bookId, int $userId, array $data): bool
    {
        $assignments = [];
        $values = [':id' => $bookId, ':user_id' => $userId];
        foreach ($data as $field => $value) {
            if (!in_array($field, self::FIELDS, true)) {
                throw new InvalidArgumentException('Campo de libro no permitido');
            }
            $assignments[] = "`$field` = :$field";
            $values[':' . $field] = $value;
        }

        $db = ConexionPDO::connect();
        $statement = $db->prepare(
            'UPDATE libro SET ' . implode(', ', $assignments)
            . ' WHERE id = :id AND cod_usuario = :user_id'
        );
        $statement->execute($values);
        if ($statement->rowCount() > 0) {
            return true;
        }

        return ConexionPDO::query(
            'SELECT 1 FROM libro WHERE id = :id AND cod_usuario = :user_id LIMIT 1',
            [':id' => $bookId, ':user_id' => $userId]
        ) !== [];
    }

    public static function deleteForOwner(int $bookId, int $userId): bool
    {
        $statement = ConexionPDO::connect()->prepare(
            'DELETE FROM libro WHERE id = :id AND cod_usuario = :user_id'
        );
        $statement->execute([':id' => $bookId, ':user_id' => $userId]);
        return $statement->rowCount() === 1;
    }
}
