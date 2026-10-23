<?php
require_once "config.php";

class ConexionPDO
{
    private static ?PDO $cnn = null;
    private static bool $schemaReady = false;

    public static function connect(): PDO
    {
        if (self::$cnn === null) {
            $pdo = 'mysql:host=' . HOST . ';port=' . PORT . ';dbname=' . DATABASE . ';' . CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            try {
                self::$cnn = new PDO($pdo, USERNAME, PASSWORD, $options);
            } catch (PDOException $error) {
                die("ERROR " . $error->getMessage());
            }
        }

        self::ensureSchema(self::$cnn);
        return self::$cnn;
    }

    private static function ensureSchema(PDO $db): void
    {
        if (self::$schemaReady) {
            return;
        }

        $db->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT NOT NULL AUTO_INCREMENT,
            username VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol VARCHAR(50) NOT NULL DEFAULT 'Lector',
            activo TINYINT(1) NOT NULL DEFAULT 1,
            fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_usuarios_username (username)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $db->exec("UPDATE usuarios SET rol = 'Autor' WHERE rol <> 'Lector'");

        $legacyUser = $db->prepare("SELECT id FROM usuarios WHERE username = :username LIMIT 1");
        $legacyUser->execute(['username' => 'maria_personal']);
        $legacyUserId = $legacyUser->fetchColumn();
        if ($legacyUserId !== false) {
            $authorUsername = $db->prepare("SELECT id FROM usuarios WHERE username = :username LIMIT 1");
            $authorUsername->execute(['username' => 'maria_autora']);
            $username = $authorUsername->fetchColumn() === false
                ? 'maria_autora'
                : 'autora_' . $legacyUserId;

            $renameUser = $db->prepare("UPDATE usuarios SET username = :new_username WHERE id = :id");
            $renameUser->execute([
                'new_username' => $username,
                'id' => $legacyUserId
            ]);
        }

        $userCount = (int) $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
        if ($userCount === 0) {
            $insert = $db->prepare(
                "INSERT INTO usuarios (username, password, rol, activo) VALUES (:username, :password, :rol, :activo)"
            );
            foreach ([
                ["admin_jose", "Autor"],
                ["maria_autora", "Autor"],
                ["carlos_lector", "Lector"]
            ] as [$username, $rol]) {
                $insert->execute([
                    "username" => $username,
                    "password" => password_hash(bin2hex(random_bytes(24)), PASSWORD_DEFAULT),
                    "rol" => $rol,
                    "activo" => 1
                ]);
            }
        }

        self::$schemaReady = true;
    }

    public static function query(string $sql, array $param = []): array
    {
        try {
            $stmt = self::connect()->prepare($sql);
            $stmt->execute($param);
            return $stmt->fetchAll();
        } catch (Throwable $error) {
            throw new RuntimeException("Existe error al consultar datos", 0, $error);
        }
    }

    public static function execute(string $sql, array $param = [], bool $id = false)
    {
        try {
            $db = self::connect();
            $stmt = $db->prepare($sql);
            $result = $stmt->execute($param);
            return $id ? $db->lastInsertId() : $result;
        } catch (Exception $e) {
            throw new RuntimeException("Existe error al procesar datos", 0, $e);
        }
    }
}