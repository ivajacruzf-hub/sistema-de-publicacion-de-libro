<?php
require_once __DIR__ . '/ResourceController.php';

class autorController extends ResourceController
{
    protected string $table = 'autor';
    protected array $fields = ['CI', 'nombre', 'apellidos', 'biografia'];
    protected array $requiredFields = ['CI', 'nombre', 'apellidos'];
}