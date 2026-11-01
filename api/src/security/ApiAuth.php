<?php
require_once __DIR__ . '/../config/conexionDB.php';

class ApiAuth
{
    private const TOKEN_LIFETIME = 43200;

    public static function issueToken(array $user): string
    {
        $payload = self::encode([
            'sub' => (int) $user['id'],
            'exp' => time() + self::TOKEN_LIFETIME
        ]);
        $signature = self::encodeSignature(hash_hmac('sha256', $payload, self::secret(), true));
        return $payload . '.' . $signature;
    }

    public static function requireAuthor(): ?array
    {
        try {
            $secret = self::secret();
        } catch (RuntimeException $error) {
            error_log('Configuración de autenticación inválida: ' . $error->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'La autenticación de la API no está configurada'], JSON_UNESCAPED_UNICODE);
            return null;
        }

        $token = self::bearerToken();
        if ($token === null) {
            self::deny(401, 'Se requiere una sesión válida');
            return null;
        }

        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            self::deny(401, 'La sesión no es válida o ha expirado');
            return null;
        }

        [$payload, $signature] = $parts;
        $expectedSignature = self::encodeSignature(hash_hmac('sha256', $payload, $secret, true));
        if (!hash_equals($expectedSignature, $signature)) {
            self::deny(401, 'La sesión no es válida o ha expirado');
            return null;
        }

        $claims = json_decode(self::decode($payload), true);
        if (
            !is_array($claims)
            || !isset($claims['sub'], $claims['exp'])
            || !filter_var($claims['sub'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            || !is_int($claims['exp'])
            || $claims['exp'] <= time()
        ) {
            self::deny(401, 'La sesión no es válida o ha expirado');
            return null;
        }

        try {
            $users = ConexionPDO::query(
                'SELECT id, username, rol, activo FROM usuarios WHERE id = :id LIMIT 1',
                [':id' => (int) $claims['sub']]
            );
        } catch (Throwable $error) {
            error_log('Error al validar la sesión de API: ' . $error);
            http_response_code(500);
            echo json_encode([
                'error' => 'No se pudo validar la sesión. ' . ConexionPDO::connectionHint($error)
            ], JSON_UNESCAPED_UNICODE);
            return null;
        }
        if ($users === [] || !(bool) $users[0]['activo']) {
            self::deny(401, 'La cuenta no está disponible');
            return null;
        }
        if ($users[0]['rol'] !== 'Autor') {
            self::deny(403, 'Solo los autores pueden administrar sus libros');
            return null;
        }

        return $users[0];
    }

    private static function bearerToken(): ?string
    {
        $headers = function_exists('getallheaders') ? getallheaders() : [];
        $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (!preg_match('/^Bearer\s+([A-Za-z0-9._-]+)$/i', trim($authorization), $matches)) {
            return null;
        }
        return $matches[1];
    }

    private static function secret(): string
    {
        if (!defined('API_TOKEN_SECRET') || strlen(API_TOKEN_SECRET) < 32) {
            throw new RuntimeException('API_TOKEN_SECRET debe configurarse con al menos 32 caracteres aleatorios');
        }
        return API_TOKEN_SECRET;
    }

    private static function encode(array $payload): string
    {
        return self::encodeSignature(json_encode($payload, JSON_THROW_ON_ERROR));
    }

    private static function encodeSignature(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function decode(string $value): string
    {
        $decoded = base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4), true);
        return $decoded === false ? '' : $decoded;
    }

    private static function deny(int $status, string $message): void
    {
        http_response_code($status);
        echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    }
}
