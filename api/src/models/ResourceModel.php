<?php
require_once __DIR__ . '/../config/conexionDB.php';

class ResourceModel
{
    public static function all(string $table): array
    {
        return ConexionPDO::query("SELECT * FROM `$table`");
    }

    public static function create(string $table, array $data)
    {
        $columns = array_keys($data);
        $quotedColumns = array_map(static fn($column) => "`$column`", $columns);
        $parameters = array_map(static fn($column) => ":$column", $columns);
        $values = [];
        foreach ($data as $column => $value) {
            $values[":$column"] = $value;
        }

        $sql = "INSERT INTO `$table` (" . implode(', ', $quotedColumns) . ') VALUES (' . implode(', ', $parameters) . ')';
        return ConexionPDO::execute($sql, $values, true);
    }

    public static function update(string $table, array $keys, array $data): bool
    {
        $data = array_diff_key($data, $keys);
        if ($data === [] || !self::exists($table, $keys)) {
            return false;
        }

        $assignments = [];
        $parameters = [];
        foreach ($data as $column => $value) {
            $assignments[] = "`$column` = :set_$column";
            $parameters[":set_$column"] = $value;
        }
        $where = self::where($keys, $parameters);
        ConexionPDO::execute("UPDATE `$table` SET " . implode(', ', $assignments) . " WHERE $where", $parameters);
        return true;
    }

    public static function delete(string $table, array $keys): bool
    {
        if (!self::exists($table, $keys)) {
            return false;
        }

        $parameters = [];
        $where = self::where($keys, $parameters);
        ConexionPDO::execute("DELETE FROM `$table` WHERE $where", $parameters);
        return true;
    }

    private static function exists(string $table, array $keys): bool
    {
        $parameters = [];
        $where = self::where($keys, $parameters);
        return ConexionPDO::query("SELECT 1 FROM `$table` WHERE $where LIMIT 1", $parameters) !== [];
    }

    private static function where(array $keys, array &$parameters): string
    {
        $conditions = [];
        foreach ($keys as $column => $value) {
            $conditions[] = "`$column` = :key_$column";
            $parameters[":key_$column"] = $value;
        }
        return implode(' AND ', $conditions);
    }
}