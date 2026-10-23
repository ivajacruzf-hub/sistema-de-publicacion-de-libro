<?php
require_once __DIR__ . '/ResourceController.php';

class lectorController extends ResourceController
{
    protected string $table = 'lector';
    protected array $fields = ['CI', 'nombre', 'apellidos', 'email'];
    protected array $requiredFields = ['CI', 'nombre', 'apellidos', 'email'];

    protected function validateValues(array $data): ?string
    {
        if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) === false) {
            return 'El email no tiene un formato válido';
        }
        return null;
    }
}