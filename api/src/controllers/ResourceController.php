<?php
require_once __DIR__ . '/../models/ResourceModel.php';

abstract class ResourceController
{
    protected string $table;
    protected array $fields = [];
    protected array $keyFields = ['id'];
    protected array $requiredFields = [];

    public function getAll(): void
    {
        try {
            $this->respond(ResourceModel::all($this->table));
        } catch (Throwable $error) {
            http_response_code(500);
            $this->respond(['error' => 'No se pudieron consultar los registros']);
        }
    }

    public function add(): void
    {
        $data = $this->readJson();
        if ($data === null || !$this->validate($data, true)) {
            return;
        }

        try {
            $id = ResourceModel::create($this->table, $data);
            http_response_code(201);
            $this->respond(['id' => $id, 'message' => 'Registro creado correctamente']);
        } catch (Throwable $error) {
            http_response_code(409);
            $this->respond(['error' => 'No se pudo crear el registro; revisa que las referencias y valores sean válidos']);
        }
    }

    public function update(...$ids): void
    {
        $keys = $this->routeKeys($ids);
        $data = $this->readJson();
        if ($keys === null || $data === null || !$this->validate($data, false)) {
            return;
        }

        try {
            if (!ResourceModel::update($this->table, $keys, $data)) {
                http_response_code(404);
                $this->respond(['error' => 'Registro no encontrado o no se enviaron campos para actualizar']);
                return;
            }
            $this->respond(['message' => 'Registro actualizado correctamente']);
        } catch (Throwable $error) {
            http_response_code(409);
            $this->respond(['error' => 'No se pudo actualizar el registro; revisa que los valores sean válidos']);
        }
    }

    public function delete(...$ids): void
    {
        $keys = $this->routeKeys($ids);
        if ($keys === null) {
            return;
        }

        try {
            if (!ResourceModel::delete($this->table, $keys)) {
                http_response_code(404);
                $this->respond(['error' => 'Registro no encontrado']);
                return;
            }
            $this->respond(['message' => 'Registro eliminado correctamente']);
        } catch (Throwable $error) {
            http_response_code(409);
            $this->respond(['error' => 'No se pudo eliminar el registro; puede tener referencias asociadas']);
        }
    }

    private function readJson(): ?array
    {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            $this->respond(['error' => 'El cuerpo debe ser un objeto JSON válido']);
            return null;
        }
        return $data;
    }

    private function validate(array &$data, bool $creating): bool
    {
        $unknown = array_diff(array_keys($data), $this->fields);
        if ($unknown !== []) {
            http_response_code(422);
            $this->respond(['error' => 'Campo(s) no permitido(s): ' . implode(', ', $unknown)]);
            return false;
        }

        if ($creating) {
            foreach ($this->requiredFields as $field) {
                if (!array_key_exists($field, $data) || $data[$field] === null || (is_string($data[$field]) && trim($data[$field]) === '')) {
                    http_response_code(422);
                    $this->respond(['error' => "El campo $field es obligatorio"]);
                    return false;
                }
            }
        } elseif ($data === []) {
            http_response_code(422);
            $this->respond(['error' => 'Envía al menos un campo permitido para actualizar']);
            return false;
        }

        $error = $this->validateValues($data);
        if ($error !== null) {
            http_response_code(422);
            $this->respond(['error' => $error]);
            return false;
        }
        return true;
    }

    protected function validateValues(array $data): ?string
    {
        return null;
    }

    private function routeKeys(array $ids): ?array
    {
        if (count($ids) !== count($this->keyFields)) {
            http_response_code(400);
            $this->respond(['error' => 'La ruta no contiene todos los identificadores requeridos']);
            return null;
        }

        return array_combine($this->keyFields, $ids);
    }

    private function respond(array $data): void
    {
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}