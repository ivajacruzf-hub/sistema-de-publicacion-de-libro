<?php
require_once __DIR__ . '/config.php';

class ConexionPDO
{
    private static ?PDO $cnn = null;
    private static bool $schemaReady = false;

    public static function connect(): PDO
    {
        if (self::$cnn === null) {
            if (HOST === '' || DATABASE === '' || USERNAME === '') {
                throw new RuntimeException('Faltan DB_HOST, DB_NAME o DB_USER en api/.env');
            }

            $pdo = 'mysql:host=' . HOST . ';port=' . PORT . ';dbname=' . DATABASE . ';' . CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            try {
                self::$cnn = new PDO($pdo, USERNAME, PASSWORD, $options);
            } catch (PDOException $error) {
                throw new RuntimeException(
                    'No se pudo conectar a MySQL. Revisa DB_HOST, DB_PORT, DB_NAME, DB_USER y DB_PASSWORD en api/.env',
                    0,
                    $error
                );
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

        self::migrateUsersTable($db);
        self::makeLegacyUserFieldsOptional($db);
        self::migrateBookOwnership($db);
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

    private static function migrateBookOwnership(PDO $db): void
    {
        $table = $db->prepare(
            "SELECT 1 FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro'"
        );
        $table->execute();
        if ($table->fetchColumn() === false) {
            return;
        }

        if (!self::hasBookColumn($db, 'genero')) {
            $db->exec('ALTER TABLE libro ADD genero VARCHAR(100) NULL');
        }
        if (!self::hasBookColumn($db, 'cod_usuario')) {
            $db->exec('ALTER TABLE libro ADD cod_usuario INT NULL');
        }

        $indexes = $db->query('SHOW INDEX FROM libro')->fetchAll(PDO::FETCH_ASSOC);
        $hasOwnerIndex = false;
        foreach ($indexes as $index) {
            if ($index['Column_name'] === 'cod_usuario') {
                $hasOwnerIndex = true;
                break;
            }
        }
        if (!$hasOwnerIndex) {
            $db->exec('ALTER TABLE libro ADD INDEX idx_libro_cod_usuario (cod_usuario)');
        }

        $foreignKey = $db->prepare(
            "SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'libro'
               AND COLUMN_NAME = 'cod_usuario'
               AND REFERENCED_TABLE_NAME = 'usuarios'"
        );
        $foreignKey->execute();
        if ($foreignKey->fetchColumn() === false) {
            $db->exec(
                'ALTER TABLE libro
                 ADD CONSTRAINT fk_libro_usuario
                 FOREIGN KEY (cod_usuario) REFERENCES usuarios(id) ON DELETE SET NULL'
            );
        }
    }

    private static function hasBookColumn(PDO $db, string $column): bool
    {
        $statement = $db->prepare(
            "SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'libro' AND COLUMN_NAME = :column"
        );
        $statement->execute(['column' => $column]);
        return $statement->fetchColumn() !== false;
    }

    private static function migrateUsersTable(PDO $db): void
    {
        if (!self::hasUserColumn($db, 'username')) {
            $usernameSource = self::hasUserColumn($db, 'email')
                ? 'email'
                : (self::hasUserColumn($db, 'nombreUsuario') ? 'nombreUsuario' : null);
            $db->exec("ALTER TABLE usuarios ADD username VARCHAR(100) NULL");
            if ($usernameSource !== null) {
                $db->exec("UPDATE usuarios SET username = `$usernameSource` WHERE username IS NULL");
            } else {
                $db->exec("UPDATE usuarios SET username = CONCAT('usuario_', id) WHERE username IS NULL");
            }
            $db->exec("ALTER TABLE usuarios MODIFY username VARCHAR(100) NOT NULL");
        }

        if (!self::hasUserColumn($db, 'password')) {
            $passwordSource = self::hasUserColumn($db, 'password_hash') ? 'password_hash' : null;
            if ($passwordSource === null) {
                throw new RuntimeException('La tabla usuarios no tiene una columna de contraseña compatible');
            }
            $db->exec("ALTER TABLE usuarios ADD password VARCHAR(255) NULL");
            $db->exec("UPDATE usuarios SET password = `$passwordSource` WHERE password IS NULL");
            $db->exec("ALTER TABLE usuarios MODIFY password VARCHAR(255) NOT NULL");
        }

        if (!self::hasUserColumn($db, 'rol')) {
            $db->exec("ALTER TABLE usuarios ADD rol VARCHAR(50) NOT NULL DEFAULT 'Lector'");
        }
        if (!self::hasUserColumn($db, 'activo')) {
            $db->exec("ALTER TABLE usuarios ADD activo TINYINT(1) NOT NULL DEFAULT 1");
            if (self::hasUserColumn($db, 'estado')) {
                $db->exec("UPDATE usuarios SET activo = CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END");
            }
        }
        if (!self::hasUserColumn($db, 'fecha_registro')) {
            $db->exec("ALTER TABLE usuarios ADD fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
            if (self::hasUserColumn($db, 'fecha_creacion')) {
                $db->exec("UPDATE usuarios SET fecha_registro = fecha_creacion");
            }
        }

        $indexes = $db->query("SHOW INDEX FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
        $hasUniqueUsername = false;
        foreach ($indexes as $index) {
            if ($index['Column_name'] === 'username' && (int) $index['Non_unique'] === 0) {
                $hasUniqueUsername = true;
                break;
            }
        }
        if (!$hasUniqueUsername) {
            $db->exec("ALTER TABLE usuarios ADD UNIQUE KEY uq_usuarios_username (username)");
        }
    }

    private static function hasUserColumn(PDO $db, string $column): bool
    {
        $statement = $db->prepare(
            "SELECT 1 FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = :column"
        );
        $statement->execute(['column' => $column]);
        return $statement->fetchColumn() !== false;
    }

    private static function makeLegacyUserFieldsOptional(PDO $db): void
    {
        $legacyFields = ['nombre', 'apellido', 'email', 'password_hash', 'nombreUsuario'];
        $columns = $db->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_ASSOC);

        foreach ($columns as $column) {
            if (
                in_array($column['Field'], $legacyFields, true)
                && $column['Null'] === 'NO'
            ) {
                $type = $column['Type'];
                if (!preg_match('/^[a-zA-Z0-9_(), ]+$/', $type)) {
                    throw new RuntimeException('No se pudo validar el tipo de una columna heredada de usuarios');
                }
                $name = $column['Field'];
                $db->exec("ALTER TABLE usuarios MODIFY COLUMN `$name` $type NULL");
            }
        }
    }

    public static function connectionHint(Throwable $error): string
    {
        $cause = $error;
        while ($cause->getPrevious() !== null) {
            $cause = $cause->getPrevious();
        }

        $message = $cause->getMessage();
        if (str_contains($message, 'Faltan DB_HOST, DB_NAME o DB_USER')) {
            return 'Completa DB_HOST, DB_NAME y DB_USER en api/.env y reinicia la API.';
        }

        $driverCode = (string) ($cause->errorInfo[1] ?? $cause->getCode());
        $sqlState = (string) ($cause->errorInfo[0] ?? '');
        if ($driverCode === '1045') {
            return 'MySQL rechazó el acceso. Verifica DB_USER, DB_PASSWORD y los permisos de ese usuario.';
        }
        if ($driverCode === '1044') {
            return 'El usuario de MySQL no tiene permisos sobre DB_NAME.';
        }
        if ($driverCode === '1049') {
            return 'La base indicada en DB_NAME no existe. Créala o corrige DB_NAME en api/.env.';
        }
        if (in_array($driverCode, ['2002', '2003', '2005'], true)) {
            return 'No se alcanza el servidor MySQL. Verifica DB_HOST, DB_PORT y que MySQL esté iniciado.';
        }
        if ($driverCode === '1146' || $sqlState === '42S02' || str_contains($message, "doesn't exist")) {
            return 'Falta una tabla requerida. Ejecuta api/data/dbpublicaciones.sql en la base configurada en DB_NAME.';
        }
        if ($driverCode === '1062') {
            return 'Hay datos duplicados en una columna que debe ser única. Revisa los registros existentes de usuarios.';
        }
        if ($driverCode === '2054' || str_contains($message, 'authentication method')) {
            return 'El controlador MySQL no admite el método de autenticación del servidor; revisa la configuración PDO MySQL del servidor PHP.';
        }
        if ($sqlState === '42S22' || str_contains($message, 'Unknown column')) {
            return 'La estructura de usuarios no es compatible. Revisa api/data/dbpublicaciones.sql y los permisos ALTER de MySQL.';
        }
        if ($sqlState === '42000' || $sqlState === '23000') {
            return 'MySQL rechazó una operación del esquema. Revisa los permisos de CREATE/ALTER/UPDATE y los datos existentes en usuarios.';
        }

        $detail = trim(preg_replace('/(password|pwd)\s*[=:]\s*\S+/i', '$1=[oculto]', $message) ?? '');
        if ($detail === '') {
            $detail = 'sin mensaje técnico disponible';
        }
        $detail = substr($detail, 0, 220);
        return 'Diagnóstico: ' . get_class($cause)
            . ($driverCode !== '' ? ", código $driverCode" : '')
            . ($sqlState !== '' ? ", SQLSTATE $sqlState" : '')
            . ". Detalle: $detail";
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