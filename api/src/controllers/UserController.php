<?php
require_once __DIR__ . '/../models/Users.php';

class UserController
{
    public function login(): void
    {
        $data = $this->readJson();
        $username = trim((string) ($data['username'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $role = trim((string) ($data['rol'] ?? ''));

        if ($username === '' || $password === '' || !in_array($role, ['Lector', 'Autor'], true)) {
            http_response_code(422);
            $this->respond(['error' => 'Usuario, contraseña y rol son obligatorios']);
            return;
        }

        try {
            $user = Users::authenticate($username, $password);
            if ($user === null || !(bool) $user['activo'] || $user['rol'] !== $role) {
                http_response_code(401);
                $this->respond(['error' => 'Usuario, contraseña o rol incorrectos']);
                return;
            }

            $this->respond([
                'id' => $user['id'],
                'username' => $user['username'],
                'rol' => $user['rol']
            ]);
        } catch (Throwable $error) {
            error_log('Error al iniciar sesión: ' . $error);
            http_response_code(500);
            $this->respond(['error' => 'No se pudo iniciar sesión']);
        }
    }

    public function getAll(): void
    {
        try {
            $this->respond(Users::all());
        } catch (Throwable $error) {
            error_log('Error al consultar usuarios: ' . $error);
            http_response_code(500);
            $this->respond([
                'error' => 'No se pudieron consultar los usuarios. ' . ConexionPDO::connectionHint($error)
            ]);
        }
    }

        public function add(): void
        {
            $data = $this->readJson();
            if (!$this->validate($data, true)) {
                return;
            }

            try {
                $id = Users::add($data);
                http_response_code(201);
                $this->respond(['id' => $id, 'message' => 'Usuario creado correctamente']);
            } catch (Throwable $error) {
                error_log('Error al crear usuario: ' . $error);
                http_response_code(409);
                $cause = $error;
                while ($cause->getPrevious() !== null) {
                    $cause = $cause->getPrevious();
                }
                $message = str_contains($cause->getMessage(), 'Duplicate entry')
                    ? 'Ese username ya existe, elige otro'
                    : 'No se pudo crear el usuario';
                $this->respond(['error' => $message]);
            }
        }

        public function update(int $id): void
        {
            $data = $this->readJson();
            if (!$this->validate($data, false)) {
                return;
            }

            try {
                if (!Users::update($id, $data)) {
                    http_response_code(404);
                    $this->respond(['error' => 'Usuario no encontrado']);
                    return;
                }
                $this->respond(['message' => 'Usuario actualizado correctamente']);
            } catch (Throwable $error) {
                http_response_code(409);
                $this->respond(['error' => 'No se pudo actualizar el usuario']);
            }
        }

        public function delete(int $id): void
        {
            if (!Users::delete($id)) {
                http_response_code(404);
                $this->respond(['error' => 'Usuario no encontrado']);
                return;
            }
            $this->respond(['message' => 'Usuario eliminado correctamente']);
        }

        private function readJson(): array
        {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!is_array($data)) {
                http_response_code(400);
                $this->respond(['error' => 'El cuerpo debe ser JSON válido']);
                return [];
            }
            return $data;
        }

        private function validate(array &$data, bool $required): bool
        {
            if ($required && (empty($data['username']) || empty($data['password']))) {
                http_response_code(422);
                $this->respond(['error' => 'username y password son obligatorios']);
                return false;
            }
            if (isset($data['username'])) {
                $data['username'] = trim((string) $data['username']);
                if ($data['username'] === '') {
                    http_response_code(422);
                    $this->respond(['error' => 'username no puede estar vacío']);
                    return false;
                }
            }
            if (isset($data['password'])) {
                $data['password'] = (string) $data['password'];
                if (strlen($data['password']) < 6) {
                    http_response_code(422);
                    $this->respond(['error' => 'password debe tener al menos 6 caracteres']);
                    return false;
                }
            }
            if (isset($data['rol'])) {
                $data['rol'] = trim((string) $data['rol']);
                if (!in_array($data['rol'], ['Lector', 'Autor'], true)) {
                    http_response_code(422);
                    $this->respond(['error' => 'El rol debe ser Lector o Autor']);
                    return false;
                }
            }
            if (isset($data['activo'])) {
                $data['activo'] = (int) (bool) $data['activo'];
            }
            return true;
        }

        private function respond(array $data): void
        {
            echo json_encode($data, JSON_UNESCAPED_UNICODE);
        }
}