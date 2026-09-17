<?php
include_once __DIR__ . "/../config/conexionDB.php";

class Users
{
    public static function all(): array
    {
        return ConexionPDO::query("SELECT id, username, rol, activo, fecha_registro FROM usuarios ORDER BY id");
    }

    public static function add(array $data): string
    {
        return ConexionPDO::execute(
            "INSERT INTO usuarios (username, password, rol, activo) VALUES (:username, :password, :rol, :activo)",
            [
                ':username' => $data['username'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':rol' => $data['rol'] ?? 'Lector',
                ':activo' => $data['activo'] ?? 1
            ],
            true
        );
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $values = [':id' => $id];
        foreach (['username', 'rol', 'activo'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = :$field";
                $values[":$field"] = $data[$field];
            }
        }
        if (array_key_exists('password', $data)) {
            $fields[] = 'password = :password';
            $values[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        if ($fields === []) {
            return false;
        }
        return ConexionPDO::execute(
            "UPDATE usuarios SET " . implode(', ', $fields) . " WHERE id = :id",
            $values
        ) && self::exists($id);
    }

    public static function delete(int $id): bool
    {
        if (!self::exists($id)) {
            return false;
        }
        return ConexionPDO::execute("DELETE FROM usuarios WHERE id = :id", [':id' => $id]);
    }

    private static function exists(int $id): bool
    {
        $result = ConexionPDO::query("SELECT id FROM usuarios WHERE id = :id", [':id' => $id]);
        return isset($result[0]['id']);
    }
}